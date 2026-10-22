<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);

if (isset($_POST['guardar'])) {
    $ubicacion = trim($_POST['ubicacion']);
    $zona = $_POST['zona'];
    $capacidad = $_POST['capacidad'];
    $estado = $_POST['estado'];
    $tipo = $_POST['tipo_residuo'];
    $nivel = max(0, min(100, intval($_POST['nivel_llenado'])));
    $lat = floatval($_POST['latitud']);
    $lng = floatval($_POST['longitud']);
    $sensor = trim($_POST['codigo_sensor'] ?? '') ?: null;

    // Código automático: PREFIJO-DISTRITO-NÚMERO (ej. ORG-D4-001)
    $prefijo = prefijo_residuo($tipo);
    $ultimo = valor($conexion, "SELECT MAX(CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)) FROM contenedores WHERE codigo LIKE ?", "s", ["$prefijo-$zona-%"]);
    $codigo = $prefijo . "-" . $zona . "-" . str_pad(intval($ultimo) + 1, 3, "0", STR_PAD_LEFT);

    $ok = consulta(
        $conexion,
        "INSERT INTO contenedores (codigo, ubicacion, zona, capacidad, estado, tipo_residuo, nivel_llenado, latitud, longitud, codigo_sensor)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        "ssssssidds",
        [$codigo, $ubicacion, $zona, $capacidad, $estado, $tipo, $nivel, $lat, $lng, $sensor]
    );
    if ($ok) {
        generar_alerta_si_corresponde($conexion, $conexion->insert_id, $nivel, $codigo);
        mensaje_y_redirigir("Contenedor registrado correctamente con código: $codigo", "listar-contenedores.php");
    }
    echo "<script>alert('Error al registrar contenedor');</script>";
}
$contenedoresMapa = filas(consulta($conexion, "SELECT codigo, ubicacion, nivel_llenado, estado, latitud, longitud FROM contenedores WHERE latitud IS NOT NULL"));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Contenedor</title>
    <link rel="stylesheet" href="../assets/leaflet/leaflet.css">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="pagina">
    <div class="caja">
        <h1>Registrar Contenedor</h1>
        <p class="subtitulo">Complete los datos del nuevo contenedor. El código se genera automáticamente según el tipo de residuo y el distrito.</p>
        <form method="POST">
            <?php include __DIR__ . '/_form-contenedor.php'; ?>
            <div class="botones">
                <button type="submit" name="guardar">Guardar contenedor</button>
                <a href="listar-contenedores.php" class="btn gris">Volver</a>
            </div>
        </form>
    </div>
</body>

</html>
