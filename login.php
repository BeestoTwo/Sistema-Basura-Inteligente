<?php
session_start();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/funciones.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $password = $_POST["password"] ?? "";

    $res = consulta($conexion, "SELECT * FROM usuarios WHERE usuario = ? AND estado = 'Activo' LIMIT 1", "s", [$usuario]);
    $fila = ($res && $res !== true) ? $res->fetch_assoc() : null;

    if ($fila && password_verify($password, $fila["password"])) {
        session_regenerate_id(true);
        $_SESSION["id_usuario"] = $fila["id"];
        $_SESSION["usuario"] = $fila["usuario"];
        $_SESSION["nombre"] = $fila["nombre"];
        $_SESSION["rol"] = $fila["rol"];
        $_SESSION["id_camion"] = $fila["id_camion"];

        if ($fila["rol"] === "admin") {
            header("Location: admin/panel-admin.php");
        } else {
            header("Location: recolector/panel-recolector.php");
        }
        exit();
    }
    $error = "Usuario o contraseña incorrectos";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Inteligente de Basura</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { height: 100vh; display: flex; justify-content: center; align-items: center; background: linear-gradient(135deg, #198754, #28a745); }
        .login-box { background: white; width: 400px; padding: 40px; border-radius: 15px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.3); text-align: center; }
        h1 { color: #198754; margin-bottom: 10px; }
        p { color: gray; margin-bottom: 30px; }
        input { width: 100%; padding: 15px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 10px; font-size: 16px; }
        button { width: 100%; padding: 15px; border: none; border-radius: 10px; background: #198754; color: white; font-size: 18px; cursor: pointer; }
        button:hover { background: #146c43; }
        .error { background: #f8d7da; color: #842029; padding: 12px; border-radius: 10px; margin-bottom: 18px; font-size: 14px; }
        .volver { display: block; margin-top: 20px; color: #198754; text-decoration: none; }
    </style>
</head>

<body>
    <div class="login-box">
        <h1>🗑️ Iniciar Sesión</h1>
        <p>Sistema Inteligente de Recolección de Basura</p>
        <?php if ($error) { ?><div class="error"><?= e($error) ?></div><?php } ?>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required value="<?= e($_POST['usuario'] ?? '') ?>">
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <a href="index.php" class="volver">← Volver al Inicio</a>
    </div>
</body>

</html>
