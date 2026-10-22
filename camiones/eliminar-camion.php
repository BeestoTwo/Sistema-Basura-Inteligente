<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$id = intval($_GET['id'] ?? 0);
// Las rutas del camión quedan registradas sin camión asignado (ON DELETE SET NULL)
if ($id && consulta($conexion, "DELETE FROM camiones WHERE id=?", "i", [$id])) {
    mensaje_y_redirigir("Camión eliminado correctamente", "listar-camiones.php");
}
mensaje_y_redirigir("No se pudo eliminar el camión", "listar-camiones.php");
