<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);

// Atender alerta
if (isset($_GET['atender'])) {
    consulta($conexion, "UPDATE alertas SET estado='Atendida' WHERE id=?", "i", [intval($_GET['atender'])]);
    header("Location: monitoreo.php");
    exit();
}

// Simulador de sensores: genera una lectura nueva para cada contenedor activo
if (isset($_POST['simular'])) {
    $lista = filas(consulta($conexion, "SELECT id, codigo, nivel_llenado, bateria FROM contenedores WHERE estado <> 'Mantenimiento' AND codigo_sensor IS NOT NULL"));
    foreach ($lista as $c) {
        $nivel = min(100, intval($c['nivel_llenado']) + rand(1, 9));
        $bat = max(5, intval($c['bateria'] ?? 100) - rand(0, 1));
        consulta($conexion, "INSERT INTO lecturas_sensor (id_contenedor, nivel, bateria) VALUES (?,?,?)", "iii", [$c['id'], $nivel, $bat]);
        consulta($conexion, "UPDATE contenedores SET nivel_llenado=?, bateria=?, ultima_lectura=NOW(), estado=IF(?>=100,'Lleno',estado) WHERE id=?", "iiii", [$nivel, $bat, $nivel, $c['id']]);
        generar_alerta_si_corresponde($conexion, $c['id'], $nivel, $c['codigo']);
    }
    mensaje_y_redirigir("Se registraron " . count($lista) . " lecturas simuladas de los sensores", "monitoreo.php");
}

$sensores = filas(consulta($conexion, "SELECT id, codigo, codigo_sensor, tipo_residuo, zona, nivel_llenado, bateria, ultima_lectura, estado FROM contenedores WHERE codigo_sensor IS NOT NULL ORDER BY nivel_llenado DESC"));
$alertas = filas(consulta($conexion, "SELECT a.*, c.codigo FROM alertas a JOIN contenedores c ON a.id_contenedor=c.id WHERE a.estado='Pendiente' ORDER BY a.fecha DESC LIMIT 12"));
$totalLecturas = valor($conexion, "SELECT COUNT(*) FROM lecturas_sensor");
$lecturasHoy = valor($conexion, "SELECT COUNT(*) FROM lecturas_sensor WHERE DATE(fecha) = CURDATE()");
$bateriaBaja = count(array_filter($sensores, fn($s) => $s['bateria'] !== null && $s['bateria'] < 70));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Monitoreo de Sensores IoT</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="con-menu">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="contenido">
        <h1>Monitoreo de Sensores IoT</h1>
        <p class="subtitulo">Lecturas de nivel de llenado enviadas por los nodos instalados en los contenedores.</p>
        <div class="cards">
            <div class="card"><h3><?= count($sensores) ?></h3><p>Sensores</p><span>Instalados</span></div>
            <div class="card"><h3><?= number_format($totalLecturas, 0, ',', '.') ?></h3><p>Lecturas</p><span><?= $lecturasHoy ?> registradas hoy</span></div>
            <div class="card"><h3 style="color:#dc3545;"><?= count($alertas) ?></h3><p>Alertas pendientes</p><span>Llenado y batería</span></div>
            <div class="card"><h3 style="color:#f59f00;"><?= $bateriaBaja ?></h3><p>Batería baja</p><span>Menos de 70 %</span></div>
        </div>
        <div class="acciones-top">
            <form method="POST" style="display:inline;"><button type="submit" name="simular">📡 Simular nueva lectura de sensores</button></form>
            <span style="font-size:13px;color:#6b7280;">Los nodos reales envían sus datos a <code>api/lectura.php</code>.</span>
        </div>
        <div class="paneles" style="grid-template-columns:1.4fr 1fr;">
            <div class="panel">
                <h2>Estado de los sensores</h2>
                <div style="max-height:520px;overflow-y:auto;">
                    <table>
                        <tr><th>Sensor</th><th>Contenedor</th><th>Tipo</th><th>Nivel</th><th>Batería</th><th>Última lectura</th></tr>
                        <?php foreach ($sensores as $s) { $n = intval($s['nivel_llenado']); ?>
                            <tr>
                                <td><?= e($s['codigo_sensor']) ?></td>
                                <td><a href="../contenedores/ver-contenedor.php?id=<?= $s['id'] ?>"><?= e($s['codigo']) ?></a></td>
                                <td><?= e($s['tipo_residuo']) ?></td>
                                <td><span class="barra"><div class="<?= clase_nivel($n, $s['estado']) ?>" style="width:<?= $n ?>%"></div></span> <b><?= $n ?>%</b></td>
                                <td style="<?= $s['bateria'] < 70 ? 'color:#dc3545;font-weight:bold;' : '' ?>">🔋 <?= intval($s['bateria']) ?>%</td>
                                <td><?= e(substr($s['ultima_lectura'] ?? '', 0, 16)) ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
            <div class="panel">
                <h2>Alertas pendientes</h2>
                <?php foreach ($alertas as $a) { ?>
                    <div class="<?= $a['tipo'] === 'Contenedor lleno' ? 'alerta' : 'info' ?>" style="font-weight:normal;margin-bottom:10px;display:flex;gap:12px;align-items:center;">
                        <div style="flex:1;"><b><?= $a['tipo'] === 'Contenedor lleno' ? '🔴' : '🔋' ?> <?= e($a['tipo']) ?></b> · <?= e(substr($a['fecha'], 0, 16)) ?><br><?= e($a['mensaje']) ?></div>
                        <a href="?atender=<?= $a['id'] ?>" class="btn chico">Atender</a>
                    </div>
                <?php } ?>
                <?php if (!$alertas) { ?><div class="exito">No hay alertas pendientes.</div><?php } ?>
            </div>
        </div>
    </div>
</body>

</html>
