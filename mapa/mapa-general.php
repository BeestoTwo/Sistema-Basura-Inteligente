<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);
$contenedores = filas(consulta($conexion, "SELECT id, codigo, tipo_residuo, ubicacion, zona, capacidad, nivel_llenado, estado, latitud, longitud, bateria FROM contenedores WHERE latitud IS NOT NULL"));
$reportes = filas(consulta($conexion, "SELECT id, tipo_problema, ubicacion, estado, latitud, longitud, fecha FROM reportes_ciudadanos WHERE latitud IS NOT NULL AND estado <> 'Atendido'"));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mapa General</title>
    <link rel="stylesheet" href="../assets/leaflet/leaflet.css">
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>

<body class="con-menu">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="contenido">
        <h1>Mapa General de Contenedores</h1>
        <p class="subtitulo">Ubicación y nivel de llenado de todos los contenedores y de los reportes ciudadanos pendientes.</p>
        <div class="filtros">
            <div><label>Tipo de residuo</label>
                <select id="fTipo" onchange="dibujar()"><option value="">Todos</option><?php foreach (tipos_residuo() as $t) { ?><option><?= $t ?></option><?php } ?></select></div>
            <div><label>Nivel mínimo</label>
                <select id="fNivel" onchange="dibujar()"><option value="0">Todos</option><option value="50">50 % o más</option><option value="80">80 % o más</option></select></div>
            <div><label>&nbsp;</label><label style="font-weight:normal;"><input type="checkbox" id="fRep" checked onchange="dibujar()" style="width:auto;margin:0 6px 0 0;">Mostrar reportes ciudadanos</label></div>
            <div><label>&nbsp;</label><span id="contador" style="font-weight:bold;"></span></div>
        </div>
        <div class="panel">
            <div id="mapa" class="mapa" style="height:620px;"></div>
            <div class="leyenda">
                <span style="--c:#198754">Menos de 50 %</span><span style="--c:#f59f00">50 % – 79 %</span><span style="--c:#dc3545">80 % o más</span>
                <span style="--c:#0d6efd">Mantenimiento</span><span style="--c:#7c3aed">Reporte ciudadano</span><span style="--c:#111827">Base de camiones</span>
            </div>
        </div>
    </div>
    <script src="../assets/leaflet/leaflet.js"></script>
    <script src="../assets/js/mapa-base.js"></script>
    <script>
        var datos = <?= json_encode($contenedores, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
        var reportes = <?= json_encode($reportes, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
        var mapa = crearMapa('mapa', [-17.7880, -63.1750], 13);
        L.marker([<?= BASE_LAT ?>, <?= BASE_LNG ?>], { icon: iconoBase() }).addTo(mapa).bindPopup('<?= BASE_NOMBRE ?>');
        var capa = L.layerGroup().addTo(mapa);
        function dibujar() {
            capa.clearLayers();
            var t = document.getElementById('fTipo').value, n = parseInt(document.getElementById('fNivel').value), k = 0;
            datos.forEach(function(c) {
                if (t && c.tipo_residuo !== t) return;
                if (parseInt(c.nivel_llenado) < n) return;
                k++;
                L.marker([c.latitud, c.longitud], { icon: iconoContenedor(colorNivel(parseInt(c.nivel_llenado), c.estado), c.codigo) }).addTo(capa)
                    .bindPopup("<b>" + c.codigo + "</b><br>" + c.ubicacion + " (" + c.zona + ")<br>Tipo: " + c.tipo_residuo + "<br>Nivel: " + c.nivel_llenado + "%<br>Batería: " + (c.bateria || '—') + "%<br><a href='../contenedores/ver-contenedor.php?id=" + c.id + "'>Ver detalle</a>");
            });
            if (document.getElementById('fRep').checked) {
                reportes.forEach(function(r) {
                    L.marker([r.latitud, r.longitud], { icon: L.divIcon({ className: '', html: '<div style="background:#7c3aed;color:white;border-radius:50%;width:24px;height:24px;line-height:24px;text-align:center;font-size:13px;box-shadow:0 0 5px #333;border:2px solid white;">!</div>', iconSize: [24, 24], iconAnchor: [12, 12] }) }).addTo(capa)
                        .bindPopup("<b>Reporte ciudadano</b><br>" + r.tipo_problema + "<br>" + r.ubicacion + "<br>Estado: " + r.estado);
                });
            }
            document.getElementById('contador').innerText = k + " contenedor(es) en el mapa";
        }
        dibujar();
    </script>
</body>

</html>
