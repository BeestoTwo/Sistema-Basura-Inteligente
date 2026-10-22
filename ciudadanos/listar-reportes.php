<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
if (isset($_POST['id'], $_POST['estado'])) {
    consulta($conexion, "UPDATE reportes_ciudadanos SET estado=? WHERE id=?", "si", [$_POST['estado'], intval($_POST['id'])]);
    header("Location: listar-reportes.php");
    exit();
}
$lista = filas(consulta($conexion, "SELECT * FROM reportes_ciudadanos ORDER BY FIELD(estado,'Pendiente','En atención','Atendido'), fecha DESC"));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reportes Ciudadanos</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="con-menu">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="contenido">
        <h1>Reportes Ciudadanos</h1>
        <p class="subtitulo">Problemas reportados por la población desde el formulario público.</p>
        <div class="tabla-scroll">
            <table>
                <tr><th>Fecha</th><th>Ciudadano</th><th>Tipo de problema</th><th class="izq">Descripción</th><th class="izq">Ubicación</th><th>Estado</th><th>Actualizar</th></tr>
                <?php foreach ($lista as $r) { ?>
                    <tr>
                        <td><?= e(substr($r['fecha'], 0, 16)) ?></td>
                        <td><?= e($r['nombre']) ?><br><small><?= e($r['telefono']) ?></small></td>
                        <td><b><?= e($r['tipo_problema']) ?></b></td>
                        <td class="izq"><?= e($r['descripcion']) ?></td>
                        <td class="izq"><?= e($r['ubicacion']) ?></td>
                        <td><span class="estado <?= e(str_replace(' ', '-', $r['estado'])) ?>"><?= e($r['estado']) ?></span></td>
                        <td>
                            <form method="POST" style="display:flex;gap:6px;">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <select name="estado" style="margin:0;padding:7px;font-size:13px;min-width:125px;"><?php foreach (['Pendiente', 'En atención', 'Atendido'] as $s) { ?><option <?= $r['estado'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php } ?></select>
                                <button type="submit" class="btn chico">OK</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>

</html>
