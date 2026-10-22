<?php
$u = $u ?? ['nombre' => '', 'usuario' => '', 'rol' => 'recolector', 'id_camion' => '', 'estado' => 'Activo'];
$camiones = filas(consulta($conexion, "SELECT id, codigo, placa, tipo_residuo FROM camiones ORDER BY codigo"));
?>
<div class="fila-2">
    <div><label>Nombre completo</label><input type="text" name="nombre" required value="<?= e($u['nombre']) ?>"></div>
    <div><label>Usuario</label><input type="text" name="usuario" required value="<?= e($u['usuario']) ?>"></div>
</div>
<div class="fila-2">
    <div><label>Contraseña <?= isset($u['id']) ? '<small style="font-weight:normal;">(dejar vacío para no cambiar)</small>' : '' ?></label><input type="password" name="password" <?= isset($u['id']) ? '' : 'required' ?> minlength="6"></div>
    <div><label>Rol</label>
        <select name="rol"><option value="admin" <?= $u['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option><option value="recolector" <?= $u['rol'] === 'recolector' ? 'selected' : '' ?>>Recolector</option></select></div>
</div>
<div class="fila-2">
    <div><label>Camión asignado (recolectores)</label>
        <select name="id_camion"><option value="">Ninguno</option>
            <?php foreach ($camiones as $c) { ?><option value="<?= $c['id'] ?>" <?= $u['id_camion'] == $c['id'] ? 'selected' : '' ?>><?= e($c['codigo'] . ' · ' . $c['placa'] . ' · ' . $c['tipo_residuo']) ?></option><?php } ?>
        </select></div>
    <div><label>Estado</label>
        <select name="estado"><?php foreach (['Activo', 'Inactivo'] as $s) { ?><option <?= $u['estado'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php } ?></select></div>
</div>
