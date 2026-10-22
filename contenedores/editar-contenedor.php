<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);

$id = intval($_GET['id'] ?? 0);
$res = consulta($conexion, "SELECT * FROM contenedores WHERE id = ?", "i", [$id]);
$c = ($res && $res !== true) ? $res->fetch_assoc() : null;
if (!$c) {
    header("Location: listar-contenedores.php");
    exit();
}

if (isset($_POST['actualizar'])) {
    $nivel = max(0, min(100, intval($_POST['nivel_llenado'])));
    $ok = consulta(
        $conexion,
        "UPDATE contenedores SET ubicacion=?, zona=?, capacidad=?, estado=?, tipo_residuo=?, nivel_llenado=?, latitud=?, longitud=?, codigo_sensor=? WHERE id=?",
        "sssssiddsi",
        [trim($_POST['ubicacion']), $_POST['zona'], $_POST['capacidad'], $_POST['estado'], $_POST['tipo_residuo'], $nivel, floatval($_POST['latitud']), floatval($_POST['longitud']), trim($_POST['codigo_sensor'] ?? '') ?: null, $id]
    );
    if ($ok) {
        generar_alerta_si_corresponde($conexion, $id, $nivel, $c['codigo']);
        mensaje_y_redirigir("Contenedor actualizado correctamente", "listar-contenedores.php");
    }
    echo "<script>alert('Error al actualizar');</script>";
}
$contenedoresMapa = filas(consulta($conexion, "SELECT codigo, ubicacion, nivel_llenado, estado, latitud, longitud FROM contenedores WHERE latitud IS NOT NULL AND id <> ?", "i", [$id]));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Contenedor</title>
    <link rel="stylesheet" href="../assets/leaflet/leaflet.css">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="pagina">
    <div class="caja">
        <h1>Editar Contenedor <?= e($c['codigo']) ?></h1>
        <p class="subtitulo">Modifique los datos y presione Actualizar.</p>
        <form method="POST">
            <?php include __DIR__ . '/_form-contenedor.php'; ?>
            <div class="botones">
                <button type="submit" name="actualizar">Actualizar</button>
                <a href="listar-contenedores.php" class="btn gris">Volver</a>
            </div>
        </form>
    </div>
</body>

</html>
