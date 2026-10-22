<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['recolector']);
$idCamion = intval($_SESSION['id_camion'] ?? 0);

// Marcar contenedor como recolectado
if (isset($_POST['recolectar'])) {
    $idDet = intval($_POST['recolectar']);
    $det = consulta($conexion, "SELECT d.id_contenedor, d.id_ruta FROM detalle_ruta d JOIN rutas r ON d.id_ruta=r.id WHERE d.id=? AND r.id_camion=?", "ii", [$idDet, $idCamion]);
    $f = ($det && $det !== true) ? $det->fetch_assoc() : null;
    if ($f) {
        consulta($conexion, "UPDATE detalle_ruta SET recolectado=1, hora_recoleccion=NOW() WHERE id=?", "i", [$idDet]);
        consulta($conexion, "UPDATE contenedores SET nivel_llenado=0, estado='Activo' WHERE id=?", "i", [$f['id_contenedor']]);
        consulta($conexion, "UPDATE alertas SET estado='Atendida' WHERE id_contenedor=? AND tipo='Contenedor lleno'", "i", [$f['id_contenedor']]);
        $pend = valor($conexion, "SELECT COUNT(*) FROM detalle_ruta WHERE id_ruta=? AND recolectado=0", "i", [$f['id_ruta']]);
        consulta($conexion, "UPDATE rutas SET estado=? WHERE id=?", "si", [$pend ? 'En proceso' : 'Completada', $f['id_ruta']]);
    }
    header("Location: panel-recolector.php");
    exit();
}
$res = consulta($conexion, "SELECT * FROM camiones WHERE id=?", "i", [$idCamion]);
$camion = ($res && $res !== true) ? $res->fetch_assoc() : null;
$rutas = filas(consulta($conexion, "SELECT * FROM rutas WHERE id_camion=? AND estado <> 'Completada' ORDER BY fecha, hora", "i", [$idCamion]));
$ruta = $rutas[0] ?? null;
$detalle = $ruta ? filas(consulta($conexion, "SELECT d.*, c.codigo, c.ubicacion, c.nivel_llenado, c.latitud, c.longitud, c.estado FROM detalle_ruta d JOIN contenedores c ON d.id_contenedor=c.id WHERE d.id_ruta=? ORDER BY d.orden", "i", [$ruta['id']])) : [];
$hechas = filas(consulta($conexion, "SELECT * FROM rutas WHERE id_camion=? AND estado='Completada' ORDER BY fecha DESC LIMIT 5", "i", [$idCamion]));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Recolector</title>
    <link rel="stylesheet" href="../assets/leaflet/leaflet.css">
    <link rel="stylesheet" href="../assets/css/estilos.css">
    <style>
        .topbar { background: linear-gradient(90deg, #198754, #0b4f31); color: white; padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; }
        .topbar a { color: white; font-weight: bold; text-decoration: none; }
        .main { padding: 26px 30px; }
    </style>
</head>

<body>
    <div class="topbar">
        <div><b style="font-size:20px;">🚛 Panel del Recolector</b><br><small><?= e($_SESSION['nombre']) ?> · <?= $camion ? e($camion['codigo'] . ' · ' . $camion['placa'] . ' · ' . $camion['tipo_residuo']) : 'Sin camión asignado' ?></small></div>
        <a href="../logout.php">🚪 Cerrar sesión</a>
    </div>
    <div class="main">
        <?php if (!$ruta) { ?>
            <div class="exito">No tiene rutas pendientes. ¡Buen trabajo!</div>
        <?php } else { $hechos = count(array_filter($detalle, fn($d) => $d['recolectado'])); ?>
            <h1><?= e($ruta['nombre_ruta']) ?></h1>
            <p class="subtitulo"><?= e($ruta['fecha']) ?> · Inicio <?= e(substr($ruta['hora'], 0, 5)) ?> · <?= e($ruta['distancia']) ?> km · <?= e($ruta['tiempo_estimado']) ?> · Avance: <b><?= $hechos ?>/<?= count($detalle) ?></b></p>
            <div class="paneles">
                <div class="panel">
                    <h2>Contenedores de la ruta</h2>
                    <table>
                        <tr><th>#</th><th>Código</th><th class="izq">Ubicación</th><th>Nivel</th><th>Acción</th></tr>
                        <?php foreach ($detalle as $d) { ?>
                            <tr>
                                <td><?= $d['orden'] ?></td><td><b><?= e($d['codigo']) ?></b></td><td class="izq"><?= e($d['ubicacion']) ?></td>
                                <td><span class="badge <?= clase_nivel(intval($d['nivel_llenado']), $d['estado']) ?>"><?= intval($d['nivel_llenado']) ?>%</span></td>
                                <td><?php if ($d['recolectado']) { ?>✅ <?= e(substr($d['hora_recoleccion'], 11, 5)) ?><?php } else { ?>
                                    <form method="POST"><button class="btn chico" name="recolectar" value="<?= $d['id'] ?>">Recolectado</button></form><?php } ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
                <div class="panel">
                    <h2>Mapa de la ruta</h2>
                    <div id="mapa" class="mapa" style="height:420px;"></div>
                </div>
            </div>
        <?php } ?>
        <?php if ($hechas) { ?>
            <div class="panel" style="margin-top:22px;">
                <h2>Rutas completadas recientemente</h2>
                <table><tr><th class="izq">Ruta</th><th>Fecha</th><th>Distancia</th></tr>
                    <?php foreach ($hechas as $h) { ?><tr><td class="izq"><?= e($h['nombre_ruta']) ?></td><td><?= e($h['fecha']) ?></td><td><?= e($h['distancia']) ?> km</td></tr><?php } ?>
                </table>
            </div>
        <?php } ?>
    </div>
    <?php if ($ruta) { ?>
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
                L.marker(p, { icon: iconoContenedor(d.recolectado == 1 ? '#6c757d' : colorNivel(parseInt(d.nivel_llenado), d.estado), d.orden + '. ' + d.codigo) }).addTo(mapa);
            });
            pts.push(BASE);
            var linea = L.polyline(pts, { color: '#0d6efd', weight: 5, dashArray: '10,8' }).addTo(mapa);
            mapa.fitBounds(linea.getBounds(), { padding: [25, 25] });
        </script>
    <?php } ?>
</body>

</html>
