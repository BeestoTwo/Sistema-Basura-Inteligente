<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);

if (isset($_POST['guardar'])) {
    $existe = valor($conexion, "SELECT COUNT(*) FROM camiones WHERE placa=?", "s", [trim($_POST['placa'])]);
    if ($existe) {
        echo "<script>alert('Ya existe un camión con esa placa');</script>";
    } else {
        $ok = consulta(
            $conexion,
            "INSERT INTO camiones (placa, conductor, telefono_conductor, licencia, categoria_licencia, vencimiento_licencia, capacidad, tipo_residuo, estado) VALUES (?,?,?,?,?,?,?,?,?)",
            "sssssssss",
            [trim($_POST['placa']), trim($_POST['conductor']), $_POST['telefono_conductor'], $_POST['licencia'], $_POST['categoria_licencia'], $_POST['vencimiento_licencia'] ?: null, $_POST['capacidad'], $_POST['tipo_residuo'], $_POST['estado']]
        );
        if ($ok) {
            $id = $conexion->insert_id;
            $codigo = "CAM-" . str_pad($id, 3, "0", STR_PAD_LEFT);
            consulta($conexion, "UPDATE camiones SET codigo=? WHERE id=?", "si", [$codigo, $id]);
            mensaje_y_redirigir("Camión registrado correctamente con código $codigo", "listar-camiones.php");
        }
        echo "<script>alert('Error al registrar camión');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Camión</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="pagina">
    <div class="caja">
        <h1>Registrar Camión</h1>
        <p class="subtitulo">Complete los datos del camión recolector y de su conductor.</p>
        <form method="POST">
            <?php include __DIR__ . '/_form-camion.php'; ?>
            <div class="botones">
                <button type="submit" name="guardar">Guardar camión</button>
                <a href="listar-camiones.php" class="btn gris">Volver</a>
            </div>
        </form>
    </div>
</body>

</html>
