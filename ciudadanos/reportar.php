<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/funciones.php';
$enviado = false;
if (isset($_POST['enviar'])) {
    $lat = $_POST['latitud'] !== '' ? floatval($_POST['latitud']) : null;
    $lng = $_POST['longitud'] !== '' ? floatval($_POST['longitud']) : null;
    $enviado = consulta(
        $conexion,
        "INSERT INTO reportes_ciudadanos (nombre, telefono, tipo_problema, descripcion, ubicacion, latitud, longitud) VALUES (?,?,?,?,?,?,?)",
        "sssssdd",
        [trim($_POST['nombre']), trim($_POST['telefono']), $_POST['tipo_problema'], trim($_POST['descripcion']), trim($_POST['ubicacion']), $lat, $lng]
    );
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar un problema</title>
    <link rel="stylesheet" href="../assets/leaflet/leaflet.css">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="pagina" style="background:linear-gradient(135deg,#198754,#28a745);">
    <div class="caja">
        <h1>📢 Reportar un problema</h1>
        <p class="subtitulo">Ayúdenos a mantener limpia la ciudad. Su reporte llega directamente al centro de control.</p>
        <?php if ($enviado) { ?><div class="exito">✅ ¡Gracias! Su reporte fue registrado y será atendido a la brevedad.</div><?php } ?>
        <form method="POST">
            <div class="fila-2">
                <div><label>Nombre</label><input type="text" name="nombre" required placeholder="Su nombre"></div>
                <div><label>Teléfono (opcional)</label><input type="text" name="telefono" placeholder="Ej: 70012345"></div>
            </div>
            <label>Tipo de problema</label>
            <select name="tipo_problema" required>
                <option value="">Seleccione</option>
                <?php foreach (['Contenedor lleno', 'Basura en la vía pública', 'Contenedor dañado', 'Camión no pasó', 'Punto crítico'] as $t) { ?><option><?= $t ?></option><?php } ?>
            </select>
            <label>Descripción</label>
            <textarea name="descripcion" required placeholder="Describa brevemente el problema"></textarea>
            <label>Dirección o referencia</label>
            <input type="text" name="ubicacion" required placeholder="Ej: Av. Paraguá y 4.º anillo">
            <label>Marque el lugar en el mapa (opcional)</label>
            <input type="hidden" name="latitud" id="latitud"><input type="hidden" name="longitud" id="longitud">
            <div id="mapa" class="mapa" style="height:300px;margin-bottom:18px;"></div>
            <div class="botones">
                <button type="submit" name="enviar">Enviar reporte</button>
                <a href="../index.php" class="btn gris">Volver</a>
            </div>
        </form>
    </div>
    <script src="../assets/leaflet/leaflet.js"></script>
    <script src="../assets/js/mapa-base.js"></script>
    <script>
        var mapa = crearMapa('mapa', [-17.7834, -63.1821], 13), m = null;
        mapa.on('click', function(e) {
            if (m) mapa.removeLayer(m);
            m = L.marker(e.latlng).addTo(mapa);
            document.getElementById('latitud').value = e.latlng.lat.toFixed(7);
            document.getElementById('longitud').value = e.latlng.lng.toFixed(7);
        });
    </script>
</body>

</html>
