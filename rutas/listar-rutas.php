<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$rutas = filas(consulta($conexion, "
    SELECT r.*, c.placa, c.conductor,
           (SELECT COUNT(*) FROM detalle_ruta d WHERE d.id_ruta = r.id) AS total,
           (SELECT COUNT(*) FROM detalle_ruta d WHERE d.id_ruta = r.id AND d.recolectado = 1) AS hechos
    FROM rutas r LEFT JOIN camiones c ON r.id_camion = c.id
    ORDER BY r.fecha DESC, r.hora DESC"));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listar Rutas</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="con-menu">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="contenido">
        <h1>Listado de Rutas de Recolección</h1>
        <p class="subtitulo">Rutas generadas por el sistema y su avance de recolección.</p>
        <div class="acciones-top">
            <a href="crear-ruta.php" class="btn">+ Crear nueva ruta</a>
            <a href="../admin/panel-admin.php" class="btn gris">Volver al panel</a>
        </div>
        <div class="tabla-scroll">
            <table>
                <tr>
                    <th>ID</th><th>Nombre de ruta</th><th>Tipo residuo</th><th>Camión</th><th>Conductor</th><th>Fecha</th><th>Hora</th>
                    <th>Distancia</th><th>Tiempo</th><th>Avance</th><th>Estado</th><th style="min-width:170px;">Acciones</th>
                </tr>
                <?php foreach ($rutas as $r) { $pct = $r['total'] ? round($r['hechos'] * 100 / $r['total']) : 0; ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td class="izq"><b><?= e($r['nombre_ruta']) ?></b></td>
                        <td><?= e($r['tipo_residuo']) ?></td>
                        <td><?= e($r['placa'] ?? '—') ?></td>
                        <td><?= e($r['conductor'] ?? '—') ?></td>
                        <td><?= e($r['fecha']) ?></td>
                        <td><?= e(substr($r['hora'], 0, 5)) ?></td>
                        <td><?= e($r['distancia']) ?> km</td>
                        <td><?= e($r['tiempo_estimado']) ?></td>
                        <td><?= $r['hechos'] ?>/<?= $r['total'] ?><br><span class="barra"><div class="verde" style="width:<?= $pct ?>%"></div></span></td>
                        <td><span class="estado <?= e(str_replace(' ', '-', $r['estado'])) ?>"><?= e($r['estado']) ?></span></td>
                        <td>
                            <a href="ver-ruta.php?id=<?= $r['id'] ?>" class="btn chico azul">Ver ruta</a>
                            <a href="eliminar-ruta.php?id=<?= $r['id'] ?>" class="btn chico rojo" onclick="return confirm('¿Eliminar esta ruta?')">Eliminar</a>
                        </td>
                    </tr>
                <?php } ?>
                <?php if (!$rutas) { ?><tr><td colspan="12">Aún no hay rutas registradas.</td></tr><?php } ?>
            </table>
        </div>
    </div>
</body>

</html>
