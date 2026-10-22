<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$id = intval($_GET['id'] ?? 0);
$res = consulta($conexion, "SELECT * FROM camiones WHERE id=?", "i", [$id]);
$c = ($res && $res !== true) ? $res->fetch_assoc() : null;
if (!$c) { header("Location: listar-camiones.php"); exit(); }

if (isset($_POST['actualizar'])) {
    $ok = consulta(
        $conexion,
        "UPDATE camiones SET placa=?, conductor=?, telefono_conductor=?, licencia=?, categoria_licencia=?, vencimiento_licencia=?, capacidad=?, tipo_residuo=?, estado=? WHERE id=?",
        "sssssssssi",
        [trim($_POST['placa']), trim($_POST['conductor']), $_POST['telefono_conductor'], $_POST['licencia'], $_POST['categoria_licencia'], $_POST['vencimiento_licencia'] ?: null, $_POST['capacidad'], $_POST['tipo_residuo'], $_POST['estado'], $id]
    );
    if ($ok) mensaje_y_redirigir("Camión actualizado correctamente", "listar-camiones.php");
    echo "<script>alert('Error al actualizar (verifique que la placa no esté repetida)');</script>";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Camión</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="pagina">
    <div class="caja">
        <h1>Editar Camión <?= e($c['codigo']) ?></h1>
        <p class="subtitulo">Modifique los datos y presione Actualizar.</p>
        <form method="POST">
            <?php include __DIR__ . '/_form-camion.php'; ?>
            <div class="botones">
                <button type="submit" name="actualizar">Actualizar</button>
                <a href="listar-camiones.php" class="btn gris">Volver</a>
            </div>
        </form>
    </div>
</body>

</html>
