<?php
/* Formulario compartido entre registrar y editar. Requiere $c (datos) y $contenedoresMapa */
$c = $c ?? ['ubicacion' => '', 'zona' => '', 'capacidad' => '', 'tipo_residuo' => '', 'estado' => 'Activo', 'nivel_llenado' => '', 'latitud' => '', 'longitud' => '', 'codigo_sensor' => ''];
?>
<label>Ubicación</label>
<input type="text" name="ubicacion" placeholder="Ej: Av. Banzer, 4to anillo" required value="<?= e($c['ubicacion']) ?>">

<div class="fila-2">
    <div>
        <label>Distrito Municipal</label>
        <select name="zona" required>
            <option value="">Seleccione un distrito</option>
            <?php for ($i = 1; $i <= 15; $i++) { $z = "D$i"; ?>
                <option value="<?= $z ?>" <?= $c['zona'] === $z ? 'selected' : '' ?>>Distrito Municipal <?= $i ?></option>
            <?php } ?>
        </select>
    </div>
    <div>
        <label>Nivel de llenado (%)</label>
        <input type="number" name="nivel_llenado" min="0" max="100" placeholder="Ej: 80" required value="<?= e($c['nivel_llenado']) ?>">
    </div>
</div>

<label>Ubicación en el mapa <small style="font-weight:normal;color:#6b7280;">(haga clic en el mapa)</small></label>
<div class="fila-2">
    <input type="text" name="latitud" id="latitud" placeholder="Latitud" readonly required value="<?= e($c['latitud']) ?>">
    <input type="text" name="longitud" id="longitud" placeholder="Longitud" readonly required value="<?= e($c['longitud']) ?>">
</div>
<div id="mapa" class="mapa" style="height:360px;margin-bottom:20px;"></div>

<div class="fila-2">
    <div>
        <label>Capacidad del contenedor</label>
        <select name="capacidad" required>
            <option value="">Seleccione</option>
            <?php foreach (['120 Litros' => 'Pequeño', '240 Litros' => 'Mediano', '660 Litros' => 'Grande', '1100 Litros' => 'Industrial'] as $v => $t) { ?>
                <option value="<?= $v ?>" <?= $c['capacidad'] === $v ? 'selected' : '' ?>><?= $v ?> - <?= $t ?></option>
            <?php } ?>
        </select>
    </div>
    <div>
        <label>Tipo de residuo</label>
        <select name="tipo_residuo" required>
            <option value="">Seleccione un tipo</option>
            <?php foreach (['Orgánico' => '🍃', 'Plástico' => '🧴', 'Papel/Cartón' => '📄', 'Vidrio' => '🍾'] as $t => $ic) { ?>
                <option value="<?= $t ?>" <?= $c['tipo_residuo'] === $t ? 'selected' : '' ?>><?= $t ?> <?= $ic ?></option>
            <?php } ?>
        </select>
    </div>
</div>

<div class="fila-2">
    <div>
        <label>Estado</label>
        <select name="estado" required>
            <?php foreach (['Activo', 'Lleno', 'Mantenimiento'] as $s) { ?>
                <option <?= $c['estado'] === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php } ?>
        </select>
    </div>
    <div>
        <label>Código del sensor IoT (opcional)</label>
        <input type="text" name="codigo_sensor" placeholder="Ej: NODO-0060" value="<?= e($c['codigo_sensor']) ?>">
    </div>
</div>

<script src="../assets/leaflet/leaflet.js"></script>
<script src="../assets/js/mapa-base.js"></script>
<script>
    var latIni = parseFloat(document.getElementById('latitud').value);
    var lngIni = parseFloat(document.getElementById('longitud').value);
    var mapa = crearMapa('mapa', isNaN(latIni) ? [-17.7834, -63.1821] : [latIni, lngIni], 14);
    var existentes = <?= json_encode($contenedoresMapa ?? [], JSON_HEX_TAG | JSON_HEX_APOS) ?>;
    existentes.forEach(function(x) {
        L.marker([x.latitud, x.longitud], { icon: iconoContenedor(colorNivel(parseInt(x.nivel_llenado), x.estado)) }).addTo(mapa)
            .bindPopup("<b>" + x.codigo + "</b><br>" + x.ubicacion + "<br>Nivel: " + x.nivel_llenado + "%");
    });
    var marcadorNuevo = isNaN(latIni) ? null : L.marker([latIni, lngIni]).addTo(mapa);
    mapa.on('click', function(e) {
        if (marcadorNuevo) mapa.removeLayer(marcadorNuevo);
        marcadorNuevo = L.marker(e.latlng).addTo(mapa);
        document.getElementById('latitud').value = e.latlng.lat.toFixed(7);
        document.getElementById('longitud').value = e.latlng.lng.toFixed(7);
    });
</script>
