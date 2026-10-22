<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_OFF);

$conexion = mysqli_init();
$flags = 0;
if (defined('DB_SSL') && DB_SSL) {
    $conexion->ssl_set(null, null, null, null, null);
    $flags = MYSQLI_CLIENT_SSL;
}
@$conexion->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, null, $flags);

if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error .
        "<br>Verifique los datos de config.php y que la base de datos '" . DB_NAME . "' esté creada.");
}

$conexion->set_charset("utf8mb4");
