<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin', 'recolector']);
$id = intval($_GET['id'] ?? 0);
$res = consulta($conexion, "SELECT r.*, c.placa, c.conductor, c.codigo AS cod_camion FROM rutas r LEFT JOIN camiones c ON r.id_camion=c.id WHERE r.id=?", "i", [$id]);
$r = ($res && $res !== true) ? $res->fetch_assoc() : null;
if (!$r) { header("Location: listar-rutas.php"); exit(); }
if ($_SESSION['rol'] === 'recolector' && intval($r['id_camion']) !== intval($_SESSION['id_camion'])) { header("Location: ../recolector/panel-recolector.php"); exit(); }

if (isset($_POST['estado']) && $_SESSION['rol'] === 'admin') {
    consulta($conexion, "UPDATE rutas SET estado=? WHERE id=?", "si", [$_POST['estado'], $id]);
    mensaje_y_redirigir("Estado de la ruta actualizado", "ver-ruta.php?id=$id");
}
$detalle = filas(consulta($conexion, "SELECT d.*, c.codigo, c.ubicacion, c.nivel_llenado, c.latitud, c.longitud, c.estado FROM detalle_ruta d JOIN contenedores c ON d.id_contenedor=c.id WHERE d.id_ruta=? ORDER BY d.orden", "i", [$id]));
$hechos = count(array_filter($detalle, fn($d) => $d['recolectado']));
$volver = $_SESSION['rol'] === 'admin' ? 'listar-rutas.php' : '../recolector/panel-recolector.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ruta <?= e($r['nombre_ruta']) ?></title>
    <link rel="stylesheet" href="../assets/leaflet/leaflet.css">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="<?= $_SESSION['rol'] === 'admin' ? 'con-menu' : 'pagina' ?>">
    <?php if ($_SESSION['rol'] === 'admin') include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="contenido">
        <h1><?= e($r['nombre_ruta']) ?></h1>
        <p class="subtitulo"><?= e($r['tipo_residuo']) ?> · Camión <?= e($r['cod_camion'] . ' ' . $r['placa']) ?> · Conductor: <?= e($r['conductor']) ?> · <?= e($r['fecha']) ?> <?= e(substr($r['hora'], 0, 5)) ?></p>
        <div class="cards">
            <div class="card"><h3><?= count($detalle) ?></h3><p>Contenedores</p><span>En la ruta</span></div>
            <div class="card"><h3><?= $hechos ?></h3><p>Recolectados</p><span><?= count($detalle) ? round($hechos * 100 / count($detalle)) : 0 ?> % de avance</span></div>
            <div class="card"><h3><?= e($r['distancia']) ?> km</h3><p>Distancia</p><span>Estimada</span></div>
            <div class="card"><h3 style="font-size:24px;"><span class="estado <?= e(str_replace(' ', '-', $r['estado'])) ?>"><?= e($r['estado']) ?></span></h3><p>Estado</p><span>Tiempo: <?= e($r['tiempo_estimado']) ?></span></div>
        </div>
        <div class="paneles">
            <div class="panel">
                <h2>Orden de recolección</h2>
                <table>
                    <tr><th>#</th><th>Código</th><th class="izq">Ubicación</th><th>Nivel</th><th>Recolectado</th></tr>
                    <?php foreach ($detalle as $d) { ?>
                        <tr>
                            <td><?= $d['orden'] ?></td><td><b><?= e($d['codigo']) ?></b></td><td class="izq"><?= e($d['ubicacion']) ?></td>
                            <td><span class="badge <?= clase_nivel(intval($d['nivel_llenado']), $d['estado']) ?>"><?= intval($d['nivel_llenado']) ?>%</span></td>
                            <td><?= $d['recolectado'] ? '✅ ' . e(substr($d['hora_recoleccion'], 11, 5)) : '⏳' ?></td>
                        </tr>
                    <?php } ?>
                </table>
                <?php if ($_SESSION['rol'] === 'admin') { ?>
                    <form method="POST" class="filtros no-imprimir" style="margin-top:18px;">
                        <div><label>Cambiar estado</label>
                            <select name="estado"><?php foreach (['Pendiente', 'En proceso', 'Completada'] as $s) { ?><option <?= $r['estado'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php } ?></select></div>
                        <div><button type="submit">Guardar</button></div>
                    </form>
                <?php } ?>
            </div>
            <div class="panel">
                <h2>Mapa de la ruta</h2>
                <div id="mapa" class="mapa" style="height:460px;"></div>
            </div>
        </div>
        <br>
        <a href="<?= $volver ?>" class="btn gris no-imprimir">Volver</a>
        <a href="#" onclick="window.print();return false;" class="btn azul no-imprimir">🖨️ Imprimir hoja de ruta</a>
    </div>
    <script src="../assets/leaflet/leaflet.js"></script>
    <script src="../assets/js/mapa-base.js"></script>
    <script>
        var BASE = [<?= BASE_LAT ?>, <?= BASE_LNG ?>];
        var det = <?= json_encode($detalle, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
        var mapa = crearMapa('mapa', BASE, 13);
        L.marker(BASE, { icon: iconoBase() }).addTo(mapa);
        var pts = [BASE];
        det.forEach(function(d) {
            var p = [+d.latitud, +d.longitud]; pts.push(p);
            var color = d.recolectado == 1 ? '#6c757d' : colorNivel(parseInt(d.nivel_llenado), d.estado);
            L.marker(p, { icon: iconoContenedor(color, d.orden + '. ' + d.codigo) }).addTo(mapa).bindPopup('<b>' + d.codigo + '</b><br>' + d.ubicacion);
        });
        pts.push(BASE);
        var linea = L.polyline(pts, { color: '#0d6efd', weight: 5, dashArray: '10,8' }).addTo(mapa);
        mapa.fitBounds(linea.getBounds(), { padding: [30, 30] });
    </script>
</body>

</html>
