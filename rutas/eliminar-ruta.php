<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$id = intval($_GET['id'] ?? 0);
if ($id && consulta($conexion, "DELETE FROM rutas WHERE id=?", "i", [$id])) {
    mensaje_y_redirigir("Ruta eliminada correctamente", "listar-rutas.php");
}
header("Location: listar-rutas.php");
