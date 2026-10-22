<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$id = intval($_GET['id'] ?? 0);
if ($id && $id != $_SESSION['id_usuario'] && consulta($conexion, "DELETE FROM usuarios WHERE id=?", "i", [$id])) {
    mensaje_y_redirigir("Usuario eliminado", "listar-usuarios.php");
}
header("Location: listar-usuarios.php");
