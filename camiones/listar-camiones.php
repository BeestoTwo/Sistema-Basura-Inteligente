<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$lista = filas(consulta($conexion, "SELECT c.*, (SELECT COUNT(*) FROM rutas r WHERE r.id_camion = c.id) AS rutas FROM camiones c ORDER BY c.id"));
$hoy = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Camiones Registrados</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="con-menu">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="contenido">
        <h1>Camiones Registrados</h1>
        <p class="subtitulo">Flota de recolección: <b><?= count($lista) ?></b> camión(es)</p>
        <div class="acciones-top">
            <a href="registrar-camion.php" class="btn">➕ Registrar camión</a>
            <a href="../admin/panel-admin.php" class="btn gris">Volver</a>
        </div>
        <div class="tabla-scroll">
            <table>
                <tr>
                    <th>Código</th><th>Placa</th><th>Conductor</th><th>Teléfono</th><th>Capacidad</th><th>Tipo residuo</th>
                    <th>Estado</th><th>Licencia (cat.)</th><th>Vencimiento</th><th>Rutas</th><th style="min-width:200px;">Acciones</th>
                </tr>
                <?php foreach ($lista as $f) {
                    $vence = $f['vencimiento_licencia'];
                    $alertaLic = $vence && $vence < date('Y-m-d', strtotime('+60 days'));
                ?>
                    <tr>
                        <td style="white-space:nowrap;"><b><?= e($f['codigo']) ?></b></td>
                        <td><?= e($f['placa']) ?></td>
                        <td><?= e($f['conductor']) ?></td>
                        <td><?= e($f['telefono_conductor']) ?></td>
                        <td style="white-space:nowrap;"><?= e($f['capacidad']) ?></td>
                        <td><?= e($f['tipo_residuo']) ?></td>
                        <td><span class="badge <?= $f['estado'] === 'Activo' ? 'verde' : ($f['estado'] === 'Mantenimiento' ? 'amarillo' : ($f['estado'] === 'En ruta' ? 'azul' : 'gris-b')) ?>"><?= e($f['estado']) ?></span></td>
                        <td><?= e($f['licencia']) ?> (<?= e($f['categoria_licencia']) ?>)</td>
                        <td style="<?= $alertaLic ? 'color:#dc3545;font-weight:bold;' : '' ?>"><?= e($vence) ?><?= $alertaLic ? ' ⚠️' : '' ?></td>
                        <td><?= intval($f['rutas']) ?></td>
                        <td>
                            <a href="editar-camion.php?id=<?= $f['id'] ?>" class="btn chico azul">Editar</a>
                            <?php if ($f['estado'] === "Fuera de servicio") { ?>
                                <a href="reactivar-camion.php?id=<?= $f['id'] ?>" class="btn chico" onclick="return confirm('¿Desea reactivar este camión?');">Reactivar</a>
                            <?php } else { ?>
                                <a href="dar-baja-camion.php?id=<?= $f['id'] ?>" class="btn chico rojo" onclick="return confirm('¿Desea dar de baja este camión?');">Dar baja</a>
                            <?php } ?>
                            <a href="eliminar-camion.php?id=<?= $f['id'] ?>" class="btn chico rojo" onclick="return confirm('¿Eliminar definitivamente este camión?');">Eliminar</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
        <p style="font-size:13px;color:#6b7280;margin-top:10px;">⚠️ Licencias que vencen en los próximos 60 días.</p>
    </div>
</body>

</html>
