<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);

$fTipo = $_GET['tipo'] ?? '';
$fZona = $_GET['zona'] ?? '';
$fEstado = $_GET['estado'] ?? '';
$buscar = trim($_GET['buscar'] ?? '');

$sql = "SELECT * FROM contenedores WHERE 1=1";
$tipos = ""; $params = [];
if ($fTipo !== '') { $sql .= " AND tipo_residuo = ?"; $tipos .= "s"; $params[] = $fTipo; }
if ($fZona !== '') { $sql .= " AND zona = ?"; $tipos .= "s"; $params[] = $fZona; }
if ($fEstado !== '') { $sql .= " AND estado = ?"; $tipos .= "s"; $params[] = $fEstado; }
if ($buscar !== '') { $sql .= " AND (codigo LIKE ? OR ubicacion LIKE ?)"; $tipos .= "ss"; $params[] = "%$buscar%"; $params[] = "%$buscar%"; }
$sql .= " ORDER BY zona, codigo";
$lista = filas(consulta($conexion, $sql, $tipos, $params));
$zonas = filas(consulta($conexion, "SELECT DISTINCT zona FROM contenedores ORDER BY LENGTH(zona), zona"));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de Contenedores</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="con-menu">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="contenido">
        <h1>Contenedores Registrados</h1>
        <p class="subtitulo">Total: <b><?= count($lista) ?></b> contenedor(es)</p>

        <div class="acciones-top">
            <a href="registrar-contenedor.php" class="btn">➕ Registrar contenedor</a>
            <a href="../admin/panel-admin.php" class="btn gris">Volver</a>
        </div>

        <form class="filtros" method="GET">
            <div><label>Buscar</label><input type="text" name="buscar" value="<?= e($buscar) ?>" placeholder="Código o ubicación"></div>
            <div><label>Tipo de residuo</label>
                <select name="tipo"><option value="">Todos</option>
                    <?php foreach (tipos_residuo() as $t) { ?><option <?= $fTipo === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php } ?>
                </select></div>
            <div><label>Distrito</label>
                <select name="zona"><option value="">Todos</option>
                    <?php foreach ($zonas as $z) { ?><option <?= $fZona === $z['zona'] ? 'selected' : '' ?>><?= e($z['zona']) ?></option><?php } ?>
                </select></div>
            <div><label>Estado</label>
                <select name="estado"><option value="">Todos</option>
                    <?php foreach (['Activo', 'Lleno', 'Mantenimiento'] as $s) { ?><option <?= $fEstado === $s ? 'selected' : '' ?>><?= $s ?></option><?php } ?>
                </select></div>
            <div><button type="submit">Filtrar</button> <a class="btn gris" href="listar-contenedores.php">Limpiar</a></div>
        </form>

        <div class="tabla-scroll">
            <table>
                <tr>
                    <th>Código</th><th>Ubicación</th><th>Distrito</th><th>Capacidad</th><th>Tipo residuo</th>
                    <th>Nivel llenado</th><th>Sensor</th><th>Estado</th><th>Fecha registro</th><th style="min-width:250px;">Acciones</th>
                </tr>
                <?php foreach ($lista as $f) { $n = intval($f['nivel_llenado']); ?>
                    <tr>
                        <td style="white-space:nowrap;"><b><?= e($f['codigo']) ?></b></td>
                        <td class="izq"><?= e($f['ubicacion']) ?></td>
                        <td><?= e($f['zona']) ?></td>
                        <td><?= e($f['capacidad']) ?></td>
                        <td><?= e($f['tipo_residuo']) ?></td>
                        <td><span class="badge <?= clase_nivel($n, $f['estado']) ?>"><?= $n ?>%</span></td>
                        <td><?= e($f['codigo_sensor'] ?: '—') ?><?= $f['bateria'] !== null ? '<br><small>🔋 ' . intval($f['bateria']) . '%</small>' : '' ?></td>
                        <td><?= e($f['estado']) ?></td>
                        <td><?= e(substr($f['fecha_registro'], 0, 10)) ?></td>
                        <td>
                            <a href="ver-contenedor.php?id=<?= $f['id'] ?>" class="btn chico gris">Ver</a>
                            <a href="editar-contenedor.php?id=<?= $f['id'] ?>" class="btn chico azul">Editar</a>
                            <?php if ($f['estado'] === "Mantenimiento") { ?>
                                <a href="reactivar-contenedor.php?id=<?= $f['id'] ?>" class="btn chico">Reactivar</a>
                            <?php } else { ?>
                                <a href="dar-baja-contenedor.php?id=<?= $f['id'] ?>" class="btn chico rojo" onclick="return confirm('¿Desea dar de baja este contenedor?');">Dar de baja</a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                <?php if (!$lista) { ?><tr><td colspan="10">No se encontraron contenedores.</td></tr><?php } ?>
            </table>
        </div>
    </div>
</body>

</html>
