<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_OFF);

$conexion = mysqli_init();
$flags = 0;
if (defined('DB_SSL') && DB_SSL) {
    $conexion->ssl_set(null, null, null, null, null);
    $flags = MYSQLI_CLIENT_SSL;
}
// Se conecta sin elegir base para poder crearla si todavía no existe (primer despliegue)
@$conexion->real_connect(DB_HOST, DB_USER, DB_PASS, null, DB_PORT, null, $flags);

if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error .
        "<br>Verifique los datos de config.php y que el servidor MySQL esté disponible.");
}

$conexion->set_charset("utf8mb4");

/* Instalación automática: si la base o sus tablas no existen (por ejemplo, una base
   nueva en Aiven), se ejecuta database/basura_inteligente.sql una sola vez. */
$baseLista = @$conexion->select_db(DB_NAME);
if ($baseLista) {
    $res = $conexion->query("SHOW TABLES LIKE 'usuarios'");
    $baseLista = $res && $res->num_rows > 0;
}
if (!$baseLista) {
    $archivoSql = __DIR__ . '/database/basura_inteligente.sql';
    $sql = is_readable($archivoSql) ? file_get_contents($archivoSql) : false;
    if ($sql === false) {
        die("La base de datos '" . DB_NAME . "' no está creada y no se encontró database/basura_inteligente.sql.");
    }
    if (DB_NAME !== 'basura_inteligente') {
        $sql = str_replace('basura_inteligente', DB_NAME, $sql);
    }
    if ($conexion->multi_query($sql)) {
        do {
            if ($r = $conexion->store_result()) {
                $r->free();
            }
        } while ($conexion->more_results() && $conexion->next_result());
    }
    if ($conexion->errno) {
        die("Error al crear la base de datos automáticamente: " . $conexion->error);
    }
    if (!@$conexion->select_db(DB_NAME)) {
        die("No se pudo seleccionar la base de datos '" . DB_NAME . "'.");
    }
}
