<?php
$c = $c ?? ['placa' => '', 'conductor' => '', 'telefono_conductor' => '', 'licencia' => '', 'categoria_licencia' => '', 'vencimiento_licencia' => '', 'capacidad' => '', 'tipo_residuo' => '', 'estado' => 'Activo'];
?>
<div class="fila-2">
    <div><label>Placa</label><input type="text" name="placa" placeholder="Ej: 1234-ABC" required value="<?= e($c['placa']) ?>"></div>
    <div><label>Conductor</label><input type="text" name="conductor" placeholder="Ej: Juan Pérez" required value="<?= e($c['conductor']) ?>"></div>
</div>
<div class="fila-2">
    <div><label>Teléfono del conductor</label><input type="text" name="telefono_conductor" placeholder="Ej: 71234567" value="<?= e($c['telefono_conductor']) ?>"></div>
    <div><label>N.º de licencia</label><input type="text" name="licencia" placeholder="Ej: 7791090" value="<?= e($c['licencia']) ?>"></div>
</div>
<div class="fila-2">
    <div><label>Categoría de licencia</label>
        <select name="categoria_licencia">
            <option value="">Seleccione</option>
            <?php foreach (['A', 'B', 'C', 'M'] as $k) { ?><option value="<?= $k ?>" <?= $c['categoria_licencia'] === $k ? 'selected' : '' ?>>Categoría <?= $k ?></option><?php } ?>
        </select></div>
    <div><label>Vencimiento de licencia</label><input type="date" name="vencimiento_licencia" value="<?= e($c['vencimiento_licencia']) ?>"></div>
</div>
<div class="fila-2">
    <div><label>Capacidad</label>
        <select name="capacidad" required>
            <option value="">Seleccione la capacidad</option>
            <?php foreach ([2, 4, 6, 8, 10, 12, 15] as $t) { $v = "$t Toneladas"; ?><option <?= $c['capacidad'] === $v ? 'selected' : '' ?>><?= $v ?></option><?php } ?>
        </select></div>
    <div><label>Tipo de residuo que recolecta</label>
        <select name="tipo_residuo" required>
            <option value="">Seleccione el tipo de residuo</option>
            <?php foreach (tipos_residuo() as $t) { ?><option <?= $c['tipo_residuo'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php } ?>
        </select></div>
</div>
<label>Estado</label>
<select name="estado" required>
    <?php foreach (['Activo', 'En ruta', 'Mantenimiento', 'Fuera de servicio'] as $s) { ?><option <?= $c['estado'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php } ?>
</select>
