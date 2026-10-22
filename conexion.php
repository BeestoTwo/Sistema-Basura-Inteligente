<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_OFF);

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error .
        "<br>Verifique los datos de config.php y que la base de datos '" . DB_NAME . "' esté creada.");
}

$conexion->set_charset("utf8mb4");
