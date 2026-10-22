<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$id = intval($_GET['id'] ?? 0);
if ($id && consulta($conexion, "UPDATE camiones SET estado='Activo' WHERE id=?", "i", [$id])) {
    mensaje_y_redirigir("Camión reactivado correctamente", "listar-camiones.php");
}
header("Location: listar-camiones.php");
