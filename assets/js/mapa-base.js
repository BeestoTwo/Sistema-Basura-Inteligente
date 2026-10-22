/* ============================================================
   Creación de mapas Leaflet con capa de respaldo sin conexión.
   - Con internet: usa los mosaicos de OpenStreetMap.
   - Sin internet (o si OSM no responde): dibuja un mapa esquemático
     de Santa Cruz de la Sierra (anillos y avenidas principales) para
     que el sistema siga funcionando.
   ============================================================ */
var SCZ_CENTRO = [-17.7834, -63.1821];

function crearMapa(idElemento, centro, zoom) {
    var mapa = L.map(idElemento).setView(centro || SCZ_CENTRO, zoom || 13);
    var capaOSM = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    });
    var errores = 0, cargados = 0, respaldo = null;

    capaOSM.on('tileload', function () { cargados++; });
    capaOSM.on('tileerror', function () {
        errores++;
        if (!respaldo && cargados === 0 && errores >= 2) {
            respaldo = activarMapaEsquematico(mapa);
        }
    });
    capaOSM.addTo(mapa);

    // Si después de 4 segundos no cargó ningún mosaico, se activa el respaldo
    setTimeout(function () {
        if (!respaldo && cargados === 0) {
            respaldo = activarMapaEsquematico(mapa);
        }
    }, 4000);
    return mapa;
}

function puntoDesde(centro, distanciaKm, rumboGrados) {
    var R = 6371, d = distanciaKm / R, b = rumboGrados * Math.PI / 180;
    var la1 = centro[0] * Math.PI / 180, lo1 = centro[1] * Math.PI / 180;
    var la2 = Math.asin(Math.sin(la1) * Math.cos(d) + Math.cos(la1) * Math.sin(d) * Math.cos(b));
    var lo2 = lo1 + Math.atan2(Math.sin(b) * Math.sin(d) * Math.cos(la1), Math.cos(d) - Math.sin(la1) * Math.sin(la2));
    return [la2 * 180 / Math.PI, lo2 * 180 / Math.PI];
}

function activarMapaEsquematico(mapa) {
    if (!mapa.getPane('esquema')) {
        mapa.createPane('esquema');
        mapa.getPane('esquema').style.zIndex = 250;
        mapa.createPane('esquemaTexto');
        mapa.getPane('esquemaTexto').style.zIndex = 450;
        mapa.getPane('esquemaTexto').style.pointerEvents = 'none';
    }
    var V = { pane: 'esquema', interactive: false };
    var T = { pane: 'esquemaTexto', interactive: false };
    var grupo = L.layerGroup();
    mapa.getContainer().style.background = '#eef1ea';

    // Manchas de áreas verdes y zonas urbanas
    L.circle(SCZ_CENTRO, Object.assign({ radius: 9200, stroke: false, fillColor: '#f3f1ec', fillOpacity: 1 }, V)).addTo(grupo);

    // Río Piraí
    L.polyline([[-17.66, -63.200], [-17.70, -63.207], [-17.74, -63.214], [-17.77, -63.222], [-17.80, -63.229], [-17.83, -63.236], [-17.87, -63.243], [-17.91, -63.247]],
        Object.assign({ color: '#9cc3e6', weight: 9, opacity: 0.9 }, V)).addTo(grupo);

    // Avenidas radiales
    var radiales = [
        ['Av. Cristo Redentor', 352], ['Av. Banzer', 12], ['Av. Beni', 28], ['Av. Alemana', 45],
        ['Av. Mutualista', 62], ['Av. Paraguá', 82], ['Av. Virgen de Cotoca', 100], ['Av. Moscú', 122],
        ['Av. Tres Pasos al Frente', 145], ['Av. Grigotá', 168], ['Av. Santos Dumont', 188],
        ['Doble vía La Guardia', 212], ['Av. Piraí', 248], ['Av. Roca y Coronado', 268], ['Av. San Martín', 300], ['Av. Busch', 325]
    ];
    radiales.forEach(function (r) {
        var linea = [SCZ_CENTRO, puntoDesde(SCZ_CENTRO, 4.5, r[1]), puntoDesde(SCZ_CENTRO, 10.5, r[1])];
        L.polyline(linea, Object.assign({ color: '#ffffff', weight: 6, opacity: 1 }, V)).addTo(grupo);
        L.polyline(linea, Object.assign({ color: '#f2c96d', weight: 3, opacity: 1 }, V)).addTo(grupo);
        var p = puntoDesde(SCZ_CENTRO, 6.6, r[1]);
        L.marker(p, { pane: 'esquemaTexto', interactive: false, icon: L.divIcon({ className: '', html: '<div style="font-size:9px;color:#6b5a2e;white-space:nowrap;transform:translate(-50%,-50%);">' + r[0] + '</div>' }) }).addTo(grupo);
    });

    // Anillos (radios aproximados en km)
    var anillos = [0.75, 1.5, 2.4, 3.4, 4.6, 5.8, 7.0, 8.3];
    anillos.forEach(function (km, i) {
        L.circle(SCZ_CENTRO, Object.assign({ radius: km * 1000, color: '#ffffff', weight: 7, fill: false }, V)).addTo(grupo);
        L.circle(SCZ_CENTRO, Object.assign({ radius: km * 1000, color: i < 4 ? '#e9a23b' : '#f2c96d', weight: 3.5, fill: false }, V)).addTo(grupo);
        var p = puntoDesde(SCZ_CENTRO, km, 135);
        L.marker(p, { pane: 'esquemaTexto', interactive: false, icon: L.divIcon({ className: '', html: '<div style="font-size:10px;font-weight:bold;color:#8a5a12;background:rgba(255,255,255,.75);padding:0 3px;border-radius:3px;white-space:nowrap;transform:translate(-50%,-50%);">' + (i + 1) + '.º anillo</div>' }) }).addTo(grupo);
    });

    // Referencias
    var lugares = [
        ['Plaza 24 de Septiembre', SCZ_CENTRO], ['Aeropuerto El Trompillo', [-17.8116, -63.1714]],
        ['Parque Urbano', [-17.7880, -63.1700]], ['UAGRM', [-17.7760, -63.1950]], ['Equipetrol', [-17.7650, -63.1980]],
        ['Plan 3000', [-17.8290, -63.1240]], ['Villa 1.º de Mayo', [-17.7690, -63.1240]], ['Urbarí', [-17.7930, -63.2010]]
    ];
    lugares.forEach(function (l) {
        L.circleMarker(l[1], Object.assign({ radius: 3, color: '#4b5563', weight: 1, fillColor: '#4b5563', fillOpacity: 1 }, V)).addTo(grupo);
        L.marker(l[1], { pane: 'esquemaTexto', interactive: false, icon: L.divIcon({ className: '', html: '<div style="font-size:10px;color:#374151;white-space:nowrap;margin:4px 0 0 6px;">' + l[0] + '</div>' }) }).addTo(grupo);
    });

    grupo.addTo(mapa);
    mapa.attributionControl.addAttribution('Mapa esquemático sin conexión');
    return grupo;
}

/* Icono circular de color con etiqueta (código) */
function iconoContenedor(color, etiqueta) {
    var html = '<div style="text-align:center;">';
    if (etiqueta) {
        html += '<div style="background:' + color + ';color:white;padding:2px 6px;border-radius:8px;font-size:10px;font-weight:bold;margin-bottom:3px;white-space:nowrap;display:inline-block;">' + etiqueta + '</div>';
    }
    html += '<div style="background:' + color + ';width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 0 5px #333;margin:auto;"></div></div>';
    return L.divIcon({ className: '', html: html, iconSize: etiqueta ? [90, 38] : [16, 16], iconAnchor: etiqueta ? [45, 38] : [8, 8] });
}

function colorNivel(nivel, estado) {
    if (estado === 'Mantenimiento') return '#0d6efd';
    if (nivel >= 80) return '#dc3545';
    if (nivel >= 50) return '#f59f00';
    return '#198754';
}

function iconoBase() {
    return L.divIcon({
        className: '',
        html: '<div style="background:#111827;color:white;padding:3px 7px;border-radius:8px;font-size:11px;font-weight:bold;white-space:nowrap;box-shadow:0 0 5px #333;">🚛 BASE</div>',
        iconSize: [60, 22], iconAnchor: [30, 11]
    });
}

function distanciaKm(lat1, lon1, lat2, lon2) {
    var R = 6371, dLat = (lat2 - lat1) * Math.PI / 180, dLon = (lon2 - lon1) * Math.PI / 180;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}
