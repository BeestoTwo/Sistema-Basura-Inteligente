<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
if (isset($_POST['guardar'])) {
    if (valor($conexion, "SELECT COUNT(*) FROM usuarios WHERE usuario=?", "s", [trim($_POST['usuario'])])) {
        echo "<script>alert('El nombre de usuario ya existe');</script>";
    } else {
        $ok = consulta($conexion, "INSERT INTO usuarios (nombre, usuario, password, rol, id_camion, estado) VALUES (?,?,?,?,?,?)", "ssssis",
            [trim($_POST['nombre']), trim($_POST['usuario']), password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['rol'], $_POST['id_camion'] !== '' ? intval($_POST['id_camion']) : null, $_POST['estado']]);
        if ($ok) mensaje_y_redirigir("Usuario registrado correctamente", "listar-usuarios.php");
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="pagina">
    <div class="caja">
        <h1>Registrar Usuario</h1>
        <p class="subtitulo">La contraseña se guarda cifrada en la base de datos.</p>
        <form method="POST">
            <?php include __DIR__ . '/_form-usuario.php'; ?>
            <div class="botones"><button type="submit" name="guardar">Guardar usuario</button><a href="listar-usuarios.php" class="btn gris">Volver</a></div>
        </form>
    </div>
</body>

</html>
