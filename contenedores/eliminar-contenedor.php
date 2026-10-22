<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$id = intval($_GET['id'] ?? 0);
if ($id && consulta($conexion, "DELETE FROM contenedores WHERE id=?", "i", [$id])) {
    mensaje_y_redirigir("Contenedor eliminado correctamente", "listar-contenedores.php");
}
header("Location: listar-contenedores.php");
