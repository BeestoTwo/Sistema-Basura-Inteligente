<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$id = intval($_GET['id'] ?? 0);
if ($id && consulta($conexion, "UPDATE contenedores SET estado='Mantenimiento' WHERE id=?", "i", [$id])) {
    mensaje_y_redirigir("Contenedor dado de baja (en mantenimiento)", "listar-contenedores.php");
}
header("Location: listar-contenedores.php");
