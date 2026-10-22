<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);

$totalContenedores = valor($conexion, "SELECT COUNT(*) FROM contenedores");
$totalCamiones = valor($conexion, "SELECT COUNT(*) FROM camiones");
$totalRutas = valor($conexion, "SELECT COUNT(*) FROM rutas");
$contenedoresLlenos = valor($conexion, "SELECT COUNT(*) FROM contenedores WHERE nivel_llenado >= ? AND estado <> 'Mantenimiento'", "i", [UMBRAL_ALTO]);
$reportesPendientes = valor($conexion, "SELECT COUNT(*) FROM reportes_ciudadanos WHERE estado = 'Pendiente'");
$ultimosContenedores = filas(consulta($conexion, "SELECT * FROM contenedores ORDER BY nivel_llenado DESC, id DESC LIMIT 7"));
$contenedoresMapa = filas(consulta($conexion, "SELECT id, codigo, tipo_residuo, ubicacion, capacidad, nivel_llenado, estado, latitud, longitud FROM contenedores WHERE latitud IS NOT NULL AND longitud IS NOT NULL"));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Administrador</title>
    <link rel="stylesheet" href="../assets/leaflet/leaflet.css">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="con-menu">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="contenido">
        <div class="titulo">
            <h1>Panel Administrador</h1>
            <p>Gestión general del Sistema Inteligente de Recolección de Basura</p>
        </div>

        <?php if ($contenedoresLlenos > 0) { ?>
            <div class="alerta">⚠️ Hay <?= $contenedoresLlenos ?> contenedor(es) con nivel mayor o igual al <?= UMBRAL_ALTO ?>%. Requieren recolección.</div>
        <?php } ?>

        <div class="cards">
            <div class="card"><h3><?= $totalContenedores ?></h3><p>Contenedores</p><span>Registrados</span></div>
            <div class="card"><h3><?= $totalCamiones ?></h3><p>Camiones</p><span>Registrados</span></div>
            <div class="card"><h3><?= $totalRutas ?></h3><p>Rutas</p><span>Registradas</span></div>
            <div class="card"><h3 style="color:#dc3545;"><?= $contenedoresLlenos ?></h3><p>Alertas</p><span>Contenedores llenos · <?= $reportesPendientes ?> reportes ciudadanos</span></div>
        </div>

        <h2>Accesos rápidos</h2><br>
        <div class="acciones-top">
            <a href="../contenedores/registrar-contenedor.php" class="btn">➕ Registrar contenedor</a>
            <a href="../camiones/registrar-camion.php" class="btn">➕ Registrar camión</a>
            <a href="../rutas/crear-ruta.php" class="btn">🗺️ Crear ruta</a>
            <a href="../sensores/monitoreo.php" class="btn">📡 Monitoreo de sensores</a>
        </div>

        <div class="paneles">
            <div class="panel">
                <h2>Contenedores con mayor llenado</h2>
                <table>
                    <thead><tr><th>Código</th><th>Tipo</th><th>Nivel</th><th>Estado</th></tr></thead>
                    <tbody>
                        <?php foreach ($ultimosContenedores as $c) { $n = intval($c['nivel_llenado']); ?>
                            <tr>
                                <td><?= e($c['codigo']) ?></td>
                                <td><?= e($c['tipo_residuo']) ?></td>
                                <td><span class="badge <?= clase_nivel($n, $c['estado']) ?>"><?= $n ?>%</span></td>
                                <td><?= e($c['estado']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <a class="btn gris chico" style="margin-top:16px;" href="../contenedores/listar-contenedores.php">Ver todos los contenedores →</a>
            </div>

            <div class="panel">
                <h2>Mapa de contenedores</h2>
                <div id="mapaDashboard" class="mapa"></div>
                <div class="leyenda">
                    <span style="--c:#198754">Menos de 50 %</span><span style="--c:#f59f00">50 % – 79 %</span>
                    <span style="--c:#dc3545">80 % o más</span><span style="--c:#0d6efd">Mantenimiento</span>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/leaflet/leaflet.js"></script>
    <script src="../assets/js/mapa-base.js"></script>
    <script>
        var mapa = crearMapa('mapaDashboard', [-17.7834, -63.1780], 13);
        var contenedores = <?= json_encode($contenedoresMapa, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
        contenedores.forEach(function(c) {
            var color = colorNivel(parseInt(c.nivel_llenado), c.estado);
            L.marker([c.latitud, c.longitud], { icon: iconoContenedor(color) }).addTo(mapa)
                .bindPopup("<b>Código:</b> " + c.codigo + "<br><b>Tipo:</b> " + c.tipo_residuo + "<br><b>Ubicación:</b> " + c.ubicacion +
                    "<br><b>Capacidad:</b> " + c.capacidad + "<br><b>Nivel:</b> " + c.nivel_llenado + "%<br><b>Estado:</b> " + c.estado);
        });
    </script>
</body>

</html>
