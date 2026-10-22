<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$id = intval($_GET['id'] ?? 0);
$res = consulta($conexion, "SELECT * FROM usuarios WHERE id=?", "i", [$id]);
$u = ($res && $res !== true) ? $res->fetch_assoc() : null;
if (!$u) { header("Location: listar-usuarios.php"); exit(); }
if (isset($_POST['actualizar'])) {
    $camion = $_POST['id_camion'] !== '' ? intval($_POST['id_camion']) : null;
    $ok = consulta($conexion, "UPDATE usuarios SET nombre=?, usuario=?, rol=?, id_camion=?, estado=? WHERE id=?", "sssisi",
        [trim($_POST['nombre']), trim($_POST['usuario']), $_POST['rol'], $camion, $_POST['estado'], $id]);
    if ($ok && $_POST['password'] !== '') {
        consulta($conexion, "UPDATE usuarios SET password=? WHERE id=?", "si", [password_hash($_POST['password'], PASSWORD_DEFAULT), $id]);
    }
    if ($ok) mensaje_y_redirigir("Usuario actualizado correctamente", "listar-usuarios.php");
    echo "<script>alert('Error al actualizar (el usuario podría estar repetido)');</script>";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="pagina">
    <div class="caja">
        <h1>Editar Usuario</h1>
        <p class="subtitulo"><?= e($u['nombre']) ?></p>
        <form method="POST">
            <?php include __DIR__ . '/_form-usuario.php'; ?>
            <div class="botones"><button type="submit" name="actualizar">Actualizar</button><a href="listar-usuarios.php" class="btn gris">Volver</a></div>
        </form>
    </div>
</body>

</html>
