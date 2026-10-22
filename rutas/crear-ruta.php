<?php
require_once __DIR__ . '/../includes/auth.php';
requiere_rol(['admin']);

$camiones = filas(consulta($conexion, "SELECT * FROM camiones WHERE estado IN ('Activo','En ruta') ORDER BY tipo_residuo, placa"));
$listaContenedores = filas(consulta($conexion, "SELECT id, codigo, tipo_residuo, nivel_llenado, ubicacion, latitud, longitud FROM contenedores WHERE estado IN ('Activo','Lleno') AND latitud IS NOT NULL AND longitud IS NOT NULL ORDER BY nivel_llenado DESC"));

if (isset($_POST['guardar'])) {
    $ids = json_decode($_POST['contenedores_ruta'] ?? '[]', true) ?: [];
    $id_camion = intval($_POST['id_camion']);
    $tipoCamion = valor($conexion, "SELECT tipo_residuo FROM camiones WHERE id=?", "i", [$id_camion]);

    if (!$ids) {
        echo "<script>alert('La ruta no tiene contenedores.');</script>";
    } elseif ($tipoCamion !== $_POST['tipo_residuo']) {
        echo "<script>alert('El tipo de camión debe coincidir con el tipo de residuo.');</script>";
    } else {
        $conexion->begin_transaction();
        $ok = consulta(
            $conexion,
            "INSERT INTO rutas (nombre_ruta, id_camion, tipo_residuo, fecha, hora, estado, lat_inicio, lng_inicio, lat_fin, lng_fin, distancia, tiempo_estimado)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
            "sissssddddds",
            [trim($_POST['nombre_ruta']), $id_camion, $_POST['tipo_residuo'], $_POST['fecha'], $_POST['hora'], $_POST['estado'],
             BASE_LAT, BASE_LNG, floatval($_POST['lat_fin']), floatval($_POST['lng_fin']), floatval($_POST['distancia']), $_POST['tiempo_estimado']]
        );
        if ($ok) {
            $id_ruta = $conexion->insert_id;
            $orden = 1;
            foreach ($ids as $id_contenedor) {
                consulta($conexion, "INSERT INTO detalle_ruta (id_ruta, id_contenedor, orden) VALUES (?,?,?)", "iii", [$id_ruta, intval($id_contenedor), $orden++]);
            }
            $conexion->commit();
            mensaje_y_redirigir("Ruta creada correctamente con " . count($ids) . " contenedores", "ver-ruta.php?id=$id_ruta");
        }
        $conexion->rollback();
        echo "<script>alert('Error al crear ruta');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Ruta Inteligente</title>
    <link rel="stylesheet" href="../assets/leaflet/leaflet.css">
    <link rel="stylesheet" href="../assets/css/estilos.css">
    <style>
        .topbar { background: #198754; color: white; padding: 18px 35px; font-size: 20px; font-weight: bold; }
        .main { padding: 30px; }
        .layout { display: grid; grid-template-columns: 360px 1fr; gap: 25px; }
        .tarjeta { background: white; border-radius: 14px; padding: 22px; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.10); }
        .tarjeta label { margin-top: 4px; }
        #mapa { height: 560px; border-radius: 12px; margin-top: 12px; }
        .resumen { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 20px; }
        .item { background: white; border-radius: 12px; padding: 18px; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08); line-height: 1.7; }
        .resaltado { color: #198754; font-weight: bold; }
        .aviso { background: #fff3cd; color: #856404; padding: 12px; border-radius: 10px; margin-bottom: 16px; font-size: 13px; }
        .contenedor-lista { font-size: 14px; line-height: 1.8; max-height: 190px; overflow-y: auto; }
        .btn-full { width: 100%; text-align: center; margin-top: 8px; }
    </style>
</head>

<body>
    <div class="topbar">🗑️ Sistema Inteligente de Recolección de Basura</div>
    <div class="main">
        <h1 style="color:#1f2937;margin-bottom:22px;">Crear Ruta de Recolección</h1>
        <form method="POST" onsubmit="return validarRuta();">
            <div class="layout">
                <div class="tarjeta">
                    <h3 style="margin-bottom:14px;">Datos de la ruta</h3>
                    <label>Nombre de la ruta</label>
                    <input type="text" name="nombre_ruta" required placeholder="Ej: Ruta Orgánica Centro">
                    <label>Tipo de residuo</label>
                    <select name="tipo_residuo" id="tipo_residuo" required onchange="generarRuta()">
                        <option value="">Seleccione tipo</option>
                        <?php foreach (tipos_residuo() as $t) { ?><option><?= $t ?></option><?php } ?>
                    </select>
                    <label>Contenedores a incluir</label>
                    <select id="criterio" onchange="generarRuta()">
                        <option value="80">Solo prioridad alta (≥ 80 %)</option>
                        <option value="50" selected>Prioridad media y alta (≥ 50 %)</option>
                        <option value="0">Todos los contenedores del tipo</option>
                    </select>
                    <label>Camión asignado</label>
                    <select name="id_camion" id="id_camion" required onchange="validarCamion()">
                        <option value="">Seleccione camión</option>
                        <?php foreach ($camiones as $cam) { ?>
                            <option value="<?= $cam['id'] ?>" data-tipo="<?= e($cam['tipo_residuo']) ?>"><?= e($cam['placa']) ?> - <?= e($cam['conductor']) ?> - <?= e($cam['tipo_residuo']) ?></option>
                        <?php } ?>
                    </select>
                    <div class="aviso">El camión seleccionado solo podrá recolectar contenedores de su mismo tipo.</div>
                    <div class="fila-2">
                        <div><label>Fecha</label><input type="date" name="fecha" required value="<?= date('Y-m-d') ?>"></div>
                        <div><label>Hora de inicio</label><input type="time" name="hora" required value="06:00"></div>
                    </div>
                    <label>Estado</label>
                    <select name="estado" required>
                        <option>Pendiente</option><option>En proceso</option><option>Completada</option>
                    </select>
                    <input type="hidden" name="lat_fin" id="lat_fin">
                    <input type="hidden" name="lng_fin" id="lng_fin">
                    <input type="hidden" name="distancia" id="distancia">
                    <input type="hidden" name="tiempo_estimado" id="tiempo_estimado">
                    <input type="hidden" name="contenedores_ruta" id="contenedores_ruta">
                    <button type="submit" name="guardar" class="btn-full">Guardar ruta</button>
                    <a href="listar-rutas.php" class="btn gris btn-full">Volver</a>
                </div>
                <div class="tarjeta">
                    <h3>Ruta sugerida</h3>
                    <p style="font-size:14px;color:#4b5563;margin-top:4px;">El sistema selecciona los contenedores según su nivel de llenado y los ordena por cercanía desde la base (vecino más cercano) para reducir el recorrido.</p>
                    <div id="mapa"></div>
                </div>
            </div>
            <div class="resumen">
                <div class="item">
                    <h3>Resumen</h3>
                    <p>Contenedores: <span class="resaltado" id="totalContenedores">0</span></p>
                    <p>Distancia estimada: <span class="resaltado" id="distanciaTexto">0 km</span></p>
                    <p>Tiempo estimado: <span class="resaltado" id="tiempoTexto">0 min</span></p>
                    <p style="font-size:12px;color:#6b7280;" id="metodo"></p>
                </div>
                <div class="item">
                    <h3>Prioridad</h3>
                    <p><span class="badge rojo">Alta</span> ≥ 80 %</p>
                    <p><span class="badge amarillo">Media</span> 50 % – 79 %</p>
                    <p><span class="badge verde">Baja</span> menor a 50 %</p>
                </div>
                <div class="item">
                    <h3>Contenedores en ruta</h3>
                    <div class="contenedor-lista" id="listaRuta">Seleccione un tipo de residuo.</div>
                </div>
            </div>
        </form>
    </div>

    <script src="../assets/leaflet/leaflet.js"></script>
    <script src="../assets/js/mapa-base.js"></script>
    <script>
        const contenedores = <?= json_encode($listaContenedores, JSON_HEX_TAG | JSON_HEX_APOS) ?>;
        const BASE = [<?= BASE_LAT ?>, <?= BASE_LNG ?>];
        const VELOCIDAD = <?= VELOCIDAD_PROMEDIO ?>;
        let mapa = crearMapa('mapa', [-17.7900, -63.1700], 13);
        let capas = L.layerGroup().addTo(mapa);
        L.marker(BASE, { icon: iconoBase() }).addTo(mapa).bindPopup("<b><?= BASE_NOMBRE ?></b><br>Inicio / Fin de la ruta");

        function ordenarPorCercania(lista) {
            let pendientes = lista.slice(), orden = [], actual = BASE;
            while (pendientes.length) {
                let mejor = 0, dMin = Infinity;
                pendientes.forEach((c, i) => {
                    let d = distanciaKm(actual[0], actual[1], +c.latitud, +c.longitud);
                    if (d < dMin) { dMin = d; mejor = i; }
                });
                let sig = pendientes.splice(mejor, 1)[0];
                orden.push(sig); actual = [+sig.latitud, +sig.longitud];
            }
            return orden;
        }

        function mostrarResumen(km, minutos, metodo) {
            document.getElementById('distanciaTexto').innerText = km.toFixed(2) + " km";
            document.getElementById('tiempoTexto').innerText = minutos + " min";
            document.getElementById('distancia').value = km.toFixed(2);
            document.getElementById('tiempo_estimado').value = minutos + " min";
            document.getElementById('metodo').innerText = metodo;
        }

        function generarRuta() {
            capas.clearLayers();
            let tipo = document.getElementById('tipo_residuo').value;
            let minimo = parseInt(document.getElementById('criterio').value);
            if (!tipo) return;
            let seleccion = contenedores.filter(c => c.tipo_residuo === tipo && parseInt(c.nivel_llenado) >= minimo);
            let orden = ordenarPorCercania(seleccion);
            let puntos = [], ids = [], html = "";
            orden.forEach((c, i) => {
                let p = [+c.latitud, +c.longitud];
                puntos.push(p); ids.push(c.id);
                let color = colorNivel(parseInt(c.nivel_llenado), '');
                L.marker(p, { icon: iconoContenedor(color, (i + 1) + '. ' + c.codigo) }).addTo(capas)
                    .bindPopup("<b>" + c.codigo + "</b><br>Tipo: " + c.tipo_residuo + "<br>Nivel: " + c.nivel_llenado + "%<br>Ubicación: " + c.ubicacion);
                html += (i + 1) + ". " + c.codigo + " — <b>" + c.nivel_llenado + "%</b><br>";
            });
            document.getElementById('totalContenedores').innerText = puntos.length;
            document.getElementById('contenedores_ruta').value = JSON.stringify(ids);
            document.getElementById('listaRuta').innerHTML = html || "No hay contenedores de este tipo con ese nivel de llenado.";
            if (!puntos.length) { mostrarResumen(0, 0, ""); return; }
            document.getElementById('lat_fin').value = puntos[puntos.length - 1][0];
            document.getElementById('lng_fin').value = puntos[puntos.length - 1][1];
            dibujarRuta([BASE, ...puntos, BASE]);
        }

        async function dibujarRuta(recorrido) {
            // 1) Intenta trazar por calles reales con OSRM (requiere internet)
            try {
                let coords = recorrido.map(p => p[1] + "," + p[0]).join(";");
                let ctrl = new AbortController();
                setTimeout(() => ctrl.abort(), 5000);
                let r = await fetch("https://router.project-osrm.org/route/v1/driving/" + coords + "?overview=full&geometries=geojson", { signal: ctrl.signal });
                let datos = await r.json();
                let ruta = datos.routes[0];
                let linea = L.polyline(ruta.geometry.coordinates.map(c => [c[1], c[0]]), { color: '#0d6efd', weight: 6 }).addTo(capas);
                mapa.fitBounds(linea.getBounds(), { padding: [30, 30] });
                mostrarResumen(ruta.distance / 1000, Math.round(ruta.duration / 60), "Ruta calculada por calles (OSRM).");
            } catch (e) {
                // 2) Sin conexión: ruta aproximada en línea recta con factor de calles 1,3
                let km = 0;
                for (let i = 0; i < recorrido.length - 1; i++) km += distanciaKm(recorrido[i][0], recorrido[i][1], recorrido[i + 1][0], recorrido[i + 1][1]);
                km *= 1.3;
                let linea = L.polyline(recorrido, { color: '#0d6efd', weight: 5, dashArray: '10,8' }).addTo(capas);
                mapa.fitBounds(linea.getBounds(), { padding: [30, 30] });
                mostrarResumen(km, Math.round(km / VELOCIDAD * 60), "Ruta aproximada (sin conexión a OSRM): distancia en línea recta × 1,3.");
            }
        }

        function validarCamion() {
            let tipoRuta = document.getElementById('tipo_residuo').value;
            let camion = document.getElementById('id_camion');
            let tipoCamion = camion.options[camion.selectedIndex].dataset.tipo;
            if (tipoRuta && tipoCamion && tipoRuta !== tipoCamion) {
                alert("Este camión es de tipo " + tipoCamion + ". No puede recolectar " + tipoRuta + ".");
                camion.value = "";
            }
        }

        function validarRuta() {
            let camion = document.getElementById('id_camion');
            let tipoCamion = camion.options[camion.selectedIndex].dataset.tipo;
            if (document.getElementById('tipo_residuo').value !== tipoCamion) { alert("El tipo de camión debe coincidir con el tipo de residuo."); return false; }
            let ids = document.getElementById('contenedores_ruta').value;
            if (!ids || ids === "[]") { alert("No hay contenedores disponibles para esta ruta."); return false; }
            return true;
        }
    </script>
</body>

</html>
