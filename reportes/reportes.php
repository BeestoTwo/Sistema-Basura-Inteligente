<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);

$porTipo = filas(consulta($conexion, "SELECT tipo_residuo, COUNT(*) total, ROUND(AVG(nivel_llenado)) promedio, SUM(CASE WHEN nivel_llenado >= " . UMBRAL_ALTO . " THEN 1 ELSE 0 END) llenos FROM contenedores GROUP BY tipo_residuo ORDER BY total DESC"));
$porZona = filas(consulta($conexion, "SELECT zona, COUNT(*) total, ROUND(AVG(nivel_llenado)) promedio FROM contenedores GROUP BY zona ORDER BY LENGTH(zona), zona"));
$porEstado = filas(consulta($conexion, "SELECT estado, COUNT(*) total FROM contenedores GROUP BY estado"));
$rutas = filas(consulta($conexion, "SELECT estado, COUNT(*) total, ROUND(SUM(distancia),1) km FROM rutas GROUP BY estado"));
$recolectados = valor($conexion, "SELECT COUNT(*) FROM detalle_ruta WHERE recolectado=1");
$kmTotal = valor($conexion, "SELECT ROUND(SUM(distancia),1) FROM rutas") ?: 0;
$promedio = valor($conexion, "SELECT ROUND(AVG(nivel_llenado)) FROM contenedores");
$reportesC = filas(consulta($conexion, "SELECT tipo_problema, COUNT(*) total FROM reportes_ciudadanos GROUP BY tipo_problema ORDER BY total DESC"));

// Exportar a CSV (Excel)
if (isset($_GET['csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_contenedores_' . date('Ymd') . '.csv');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Código', 'Ubicación', 'Distrito', 'Tipo', 'Capacidad', 'Nivel %', 'Estado', 'Sensor', 'Batería %', 'Última lectura'], ';');
    foreach (filas(consulta($conexion, "SELECT * FROM contenedores ORDER BY zona, codigo")) as $c) {
        fputcsv($out, [$c['codigo'], $c['ubicacion'], $c['zona'], $c['tipo_residuo'], $c['capacidad'], $c['nivel_llenado'], $c['estado'], $c['codigo_sensor'], $c['bateria'], $c['ultima_lectura']], ';');
    }
    exit();
}
$maxZona = max(array_column($porZona, 'total') ?: [1]);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="con-menu">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="contenido">
        <h1>Reportes del Sistema</h1>
        <p class="subtitulo">Resumen estadístico para el seguimiento y la toma de decisiones · Generado el <?= date('d/m/Y H:i') ?></p>
        <div class="acciones-top no-imprimir">
            <a href="?csv=1" class="btn">⬇️ Exportar contenedores (CSV/Excel)</a>
            <a href="#" onclick="window.print();return false;" class="btn azul">🖨️ Imprimir / PDF</a>
        </div>
        <div class="cards">
            <div class="card"><h3><?= $promedio ?>%</h3><p>Llenado promedio</p><span>De todos los contenedores</span></div>
            <div class="card"><h3><?= $recolectados ?></h3><p>Recolecciones</p><span>Registradas en rutas</span></div>
            <div class="card"><h3><?= number_format($kmTotal, 1, ',', '.') ?></h3><p>Kilómetros</p><span>Planificados en rutas</span></div>
            <div class="card"><h3><?= array_sum(array_column($reportesC, 'total')) ?></h3><p>Reportes ciudadanos</p><span>Recibidos</span></div>
        </div>
        <div class="paneles" style="grid-template-columns:1fr 1fr;">
            <div class="panel">
                <h2>Contenedores por tipo de residuo</h2>
                <table>
                    <tr><th class="izq">Tipo</th><th>Cantidad</th><th>Llenado promedio</th><th>≥ 80 %</th></tr>
                    <?php foreach ($porTipo as $t) { ?><tr><td class="izq"><?= e($t['tipo_residuo']) ?></td><td><?= $t['total'] ?></td><td><?= $t['promedio'] ?>%</td><td><span class="badge rojo"><?= $t['llenos'] ?></span></td></tr><?php } ?>
                </table>
                <h2 style="margin-top:22px;">Contenedores por estado</h2>
                <table>
                    <tr><th class="izq">Estado</th><th>Cantidad</th></tr>
                    <?php foreach ($porEstado as $t) { ?><tr><td class="izq"><?= e($t['estado']) ?></td><td><?= $t['total'] ?></td></tr><?php } ?>
                </table>
            </div>
            <div class="panel">
                <h2>Contenedores y llenado promedio por distrito</h2>
                <div class="grafico">
                    <?php foreach ($porZona as $z) { ?>
                        <div class="fila"><span>Distrito <?= e(substr($z['zona'], 1)) ?> (<?= $z['total'] ?>)</span>
                            <div class="pista"><div class="valor" style="width:<?= $z['promedio'] ?>%;background:<?= $z['promedio'] >= 80 ? '#dc3545' : ($z['promedio'] >= 50 ? '#f59f00' : '#198754') ?>"></div></div>
                            <b><?= $z['promedio'] ?>%</b></div>
                    <?php } ?>
                </div>
            </div>
            <div class="panel">
                <h2>Rutas por estado</h2>
                <table>
                    <tr><th class="izq">Estado</th><th>Rutas</th><th>Kilómetros</th></tr>
                    <?php foreach ($rutas as $t) { ?><tr><td class="izq"><span class="estado <?= e(str_replace(' ', '-', $t['estado'])) ?>"><?= e($t['estado']) ?></span></td><td><?= $t['total'] ?></td><td><?= $t['km'] ?></td></tr><?php } ?>
                </table>
            </div>
            <div class="panel">
                <h2>Reportes ciudadanos por tipo</h2>
                <table>
                    <tr><th class="izq">Tipo de problema</th><th>Cantidad</th></tr>
                    <?php foreach ($reportesC as $t) { ?><tr><td class="izq"><?= e($t['tipo_problema']) ?></td><td><?= $t['total'] ?></td></tr><?php } ?>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
