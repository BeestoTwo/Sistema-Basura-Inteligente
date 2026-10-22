<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$id = intval($_GET['id'] ?? 0);
$res = consulta($conexion, "SELECT * FROM contenedores WHERE id=?", "i", [$id]);
$c = ($res && $res !== true) ? $res->fetch_assoc() : null;
if (!$c) { header("Location: listar-contenedores.php"); exit(); }
$lecturas = filas(consulta($conexion, "SELECT nivel, bateria, fecha FROM lecturas_sensor WHERE id_contenedor=? ORDER BY fecha DESC LIMIT 40", "i", [$id]));
$serie = array_reverse($lecturas);
$recolecciones = valor($conexion, "SELECT COUNT(*) FROM detalle_ruta WHERE id_contenedor=? AND recolectado=1", "i", [$id]);
$n = intval($c['nivel_llenado']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Contenedor <?= e($c['codigo']) ?></title>
    <link rel="stylesheet" href="../assets/leaflet/leaflet.css">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="con-menu">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="contenido">
        <h1>Contenedor <?= e($c['codigo']) ?></h1>
        <p class="subtitulo"><?= e($c['ubicacion']) ?> · Distrito <?= e($c['zona']) ?></p>
        <div class="cards">
            <div class="card"><h3 style="color:<?= $n >= UMBRAL_ALTO ? '#dc3545' : '#198754' ?>"><?= $n ?>%</h3><p>Nivel actual</p><span><?= e($c['estado']) ?></span></div>
            <div class="card"><h3><?= e($c['tipo_residuo']) ?></h3><p>Tipo de residuo</p><span><?= e($c['capacidad']) ?></span></div>
            <div class="card"><h3><?= $c['bateria'] !== null ? intval($c['bateria']) . '%' : '—' ?></h3><p>Batería del sensor</p><span><?= e($c['codigo_sensor'] ?: 'Sin sensor') ?></span></div>
            <div class="card"><h3><?= $recolecciones ?></h3><p>Recolecciones</p><span>Registradas en rutas</span></div>
        </div>
        <div class="paneles">
            <div class="panel">
                <h2>Historial de llenado (sensor)</h2>
                <svg id="grafico" viewBox="0 0 600 260" style="width:100%;height:260px;"></svg>
                <p style="font-size:13px;color:#6b7280;">Última lectura: <?= e($c['ultima_lectura'] ?? '—') ?></p>
            </div>
            <div class="panel">
                <h2>Ubicación</h2>
                <div id="mapa" class="mapa" style="height:300px;"></div>
            </div>
        </div>
        <br><a href="listar-contenedores.php" class="btn gris">Volver</a>
        <a href="editar-contenedor.php?id=<?= $id ?>" class="btn azul">Editar</a>
    </div>
    <script src="../assets/leaflet/leaflet.js"></script>
    <script src="../assets/js/mapa-base.js"></script>
    <script>
        var serie = <?= json_encode($serie) ?>;
        var svg = document.getElementById('grafico'), W = 600, H = 260, m = 35;
        var html = '';
        [0, 25, 50, 75, 100].forEach(function(v) {
            var y = H - m - (H - 2 * m) * v / 100;
            html += '<line x1="' + m + '" x2="' + (W - 10) + '" y1="' + y + '" y2="' + y + '" stroke="#e5e7eb"/>';
            html += '<text x="5" y="' + (y + 4) + '" font-size="11" fill="#6b7280">' + v + '%</text>';
        });
        var yU = H - m - (H - 2 * m) * 0.8;
        html += '<line x1="' + m + '" x2="' + (W - 10) + '" y1="' + yU + '" y2="' + yU + '" stroke="#dc3545" stroke-dasharray="5,4"/>';
        if (serie.length > 1) {
            var pts = serie.map(function(p, i) {
                var x = m + (W - m - 10) * i / (serie.length - 1);
                var y = H - m - (H - 2 * m) * p.nivel / 100;
                return x.toFixed(1) + ',' + y.toFixed(1);
            });
            html += '<polyline points="' + pts.join(' ') + '" fill="none" stroke="#198754" stroke-width="2.5"/>';
            html += '<text x="' + m + '" y="' + (H - 10) + '" font-size="11" fill="#6b7280">' + serie[0].fecha.substring(5, 16) + '</text>';
            html += '<text x="' + (W - 110) + '" y="' + (H - 10) + '" font-size="11" fill="#6b7280">' + serie[serie.length - 1].fecha.substring(5, 16) + '</text>';
        }
        svg.innerHTML = html;
        var mapa = crearMapa('mapa', [<?= floatval($c['latitud']) ?>, <?= floatval($c['longitud']) ?>], 15);
        L.marker([<?= floatval($c['latitud']) ?>, <?= floatval($c['longitud']) ?>], { icon: iconoContenedor(colorNivel(<?= $n ?>, <?= json_encode($c['estado']) ?>), <?= json_encode($c['codigo']) ?>) }).addTo(mapa);
    </script>
</body>

</html>
