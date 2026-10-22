<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$lista = filas(consulta($conexion, "SELECT u.*, c.codigo AS cod_camion, c.placa FROM usuarios u LEFT JOIN camiones c ON u.id_camion=c.id ORDER BY u.rol, u.nombre"));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="con-menu">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="contenido">
        <h1>Usuarios del Sistema</h1>
        <p class="subtitulo">Administradores y recolectores con acceso a la plataforma.</p>
        <div class="acciones-top"><a href="registrar-usuario.php" class="btn">➕ Registrar usuario</a></div>
        <div class="tabla-scroll">
            <table>
                <tr><th class="izq">Nombre</th><th>Usuario</th><th>Rol</th><th>Camión asignado</th><th>Estado</th><th>Fecha registro</th><th>Acciones</th></tr>
                <?php foreach ($lista as $u) { ?>
                    <tr>
                        <td class="izq"><?= e($u['nombre']) ?></td>
                        <td><?= e($u['usuario']) ?></td>
                        <td><span class="badge <?= $u['rol'] === 'admin' ? 'azul' : 'verde' ?>"><?= $u['rol'] === 'admin' ? 'Administrador' : 'Recolector' ?></span></td>
                        <td><?= $u['cod_camion'] ? e($u['cod_camion'] . ' · ' . $u['placa']) : '—' ?></td>
                        <td><?= e($u['estado']) ?></td>
                        <td><?= e(substr($u['fecha_registro'], 0, 10)) ?></td>
                        <td>
                            <a href="editar-usuario.php?id=<?= $u['id'] ?>" class="btn chico azul">Editar</a>
                            <?php if ($u['id'] != $_SESSION['id_usuario']) { ?>
                                <a href="eliminar-usuario.php?id=<?= $u['id'] ?>" class="btn chico rojo" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>

</html>
