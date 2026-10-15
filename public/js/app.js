// public/js/app.js

// Configura aquí tu número de WhatsApp (con código de país, ej. 521...)
const TELEFONO_WHATSAPP = "527716837879";

let propiedades = [];
let markers = {};
let polygons = {};
const carousel = document.getElementById('carousel');

// Capas de Mapa (Satélite y Calles)
const capaCalles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
});
const capaSatelite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: '© Esri Satellite'
});

// Inicializar Mapa en Vista Satélite
const map = L.map('map', {
    zoomControl: false,
    layers: [capaSatelite]
}).setView([20.1000, -98.7300], 12);

// Botón para cambiar entre Satélite y Calles (arriba a la derecha debajo de las zonas)
L.control.layers({ "🛰️ Satélite": capaSatelite, "🗺️ Mapa": capaCalles }, null, { position: 'topright' }).addTo(map);

// Cargar propiedades desde el Controlador PHP (API JSON)
async function cargarPropiedades(zona = 'todas') {
    try {
        const res = await fetch(`index.php?action=api&zona=${encodeURIComponent(zona)}`);
        propiedades = await res.json();
        renderizarPropiedades(propiedades);
    } catch (error) {
        console.error("Error cargando propiedades:", error);
    }
}

function renderizarPropiedades(lista) {
    
    carousel.innerHTML = '';
    Object.values(markers).forEach(m => map.removeLayer(m));
    markers = {};
    // Justo debajo de Object.values(markers).forEach(m => map.removeLayer(m)); agrega:
    Object.values(polygons).forEach(poly => map.removeLayer(poly));
    polygons = {};

    if (lista.length === 0) {
        carousel.innerHTML = `<div class="property-card">Sin propiedades disponibles en esta zona.</div>`;
        return;
    }

    lista.forEach((prop, index) => {
        const lat = parseFloat(prop.latitud);
        const lng = parseFloat(prop.longitud);
        const precioNum = parseFloat(prop.precio);

        // Pin en el mapa
        const claseEstado = prop.estado === 'apartado' ? 'apartado' : '';
        const icon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div class="price-pin ${claseEstado}" id="pin-${prop.id}">${prop.precio_corto}</div>`,
            iconSize: [64, 28],
            iconAnchor: [32, 14]
        });

        const marker = L.marker([lat, lng], { icon }).addTo(map);
        marker.on('click', (e) => {
            L.DomEvent.stopPropagation(e); // Evita que el clic en el pin cierre el panel
            centrarPropiedad(prop.id);
            abrirDetalle(prop);
        });
        markers[prop.id] = marker;

        // Si el terreno tiene polígono trazado, lo dibujamos sobre la vista satelital
        if (prop.poligono) {
            try {
                const coordsPoli = JSON.parse(prop.poligono);
                if (Array.isArray(coordsPoli) && coordsPoli.length >= 3) {
                    const colorPoli = prop.estado === 'apartado' ? '#eab308' : '#22c55e';
                    const poly = L.polygon(coordsPoli, {
                        color: colorPoli,
                        weight: 2,
                        fillColor: colorPoli,
                        fillOpacity: 0.3
                    }).addTo(map);
                    polygons[prop.id] = poly;
                }
            } catch (err) {}
        }

        // Tarjeta en el carrusel
        const card = document.createElement('div');
        card.className = `property-card ${index === 0 ? 'active' : ''}`;
        card.id = `card-${prop.id}`;
        card.dataset.id = prop.id;
        card.innerHTML = `
            <div class="card-header">
                <span class="card-badge">${prop.estatus_legal}</span>
                <span class="card-badge ${claseEstado}">${prop.estado.toUpperCase()}</span>
            </div>
            <div class="card-title">${prop.titulo}</div>
            <div class="card-meta">📐 ${prop.superficie_m2} m² • 📍 ${prop.zona}</div>
            <div class="card-price">$${precioNum.toLocaleString('es-MX')} MXN</div>
        `;
        
        card.addEventListener('click', (e) => {
            e.stopPropagation();
            centrarPropiedad(prop.id);
            abrirDetalle(prop);
        });

        carousel.appendChild(card);
    });

    // Centrar en la primera propiedad de la lista
    centrarPropiedad(lista[0].id);
}

function centrarPropiedad(id) {
    const prop = propiedades.find(p => parseInt(p.id) === parseInt(id));
    if (!prop) return;

    map.flyTo([parseFloat(prop.latitud), parseFloat(prop.longitud)], 16, { duration: 0.8 });

    document.querySelectorAll('.price-pin').forEach(el => el.classList.remove('active-pin'));
    document.querySelectorAll('.property-card').forEach(el => el.classList.remove('active'));

    const pinEl = document.getElementById(`pin-${prop.id}`);
    const cardEl = document.getElementById(`card-${prop.id}`);
    if (pinEl) pinEl.classList.add('active-pin');
    if (cardEl) {
        cardEl.classList.add('active');
        cardEl.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }
}

// Sincronizar el scroll del carrusel con el movimiento del mapa
let scrollTimeout;
carousel.addEventListener('scroll', () => {
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(() => {
        const center = carousel.scrollLeft + (carousel.offsetWidth / 2);
        let closestCard = null;
        let minDiff = Infinity;

        document.querySelectorAll('.property-card').forEach(card => {
            const cardCenter = card.offsetLeft + (card.offsetWidth / 2);
            const diff = Math.abs(center - cardCenter);
            if (diff < minDiff) {
                minDiff = diff;
                closestCard = card;
            }
        });

        if (closestCard && closestCard.dataset.id) {
            centrarPropiedad(closestCard.dataset.id);
        }
    }, 150);
});

function abrirDetalle(prop) {
    const sheet = document.getElementById('bottomSheet');
    const content = document.getElementById('sheetContent');
    if (!sheet || !content) return;

    const precioFormateado = parseFloat(prop.precio).toLocaleString('es-MX');
    
    const msgWhatsApp = encodeURIComponent(
        `Hola, vi en el catálogo el *${prop.titulo}* (${prop.zona}) de $${precioFormateado} MXN y me gustaría recibir más información o agendar una visita.`
    );

    const imgHtml = (prop.imagen_principal && prop.imagen_principal !== 'default.jpg')
        ? `<img src="public/img/${prop.imagen_principal}" onerror="this.style.display='none'" style="width:100%; height:190px; object-fit:cover; border-radius:14px; margin-bottom:12px;">`
        : '';

    content.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <span class="card-badge">${prop.tipo.toUpperCase()} • ${prop.estado.toUpperCase()}</span>
            <button onclick="cerrarDetalle()" style="background:#f1f5f9; border:none; border-radius:50%; width:30px; height:30px; font-weight:bold; cursor:pointer;">✕</button>
        </div>
        ${imgHtml}
        <h2 style="margin: 4px 0; color:#0f172a; font-size:1.25rem;">${prop.titulo}</h2>
        <h3 style="color:#16a34a; margin-bottom:12px; font-size:1.3rem;">
            $${precioFormateado} MXN 
            <small style="color:#64748b; font-weight:normal; font-size:0.9rem;">(${prop.superficie_m2} m²)</small>
        </h3>
        <p style="color:#334155; line-height:1.5; font-size:0.95rem;">${prop.descripcion}</p>
        <div class="trust-box">
            <strong>🛡️ Certeza Legal:</strong> ${prop.estatus_legal}<br>
            <strong>⚡ Servicios:</strong> ${prop.servicios}<br>
            <strong>📍 Ubicación:</strong> ${prop.zona}
        </div>
        <a class="btn-whatsapp" href="https://wa.me/${TELEFONO_WHATSAPP}?text=${msgWhatsApp}" target="_blank">
            💬 Pedir ubicación o agendar visita por WhatsApp
        </a>
    `;
    sheet.classList.add('open');
}

function cerrarDetalle() {
    const sheet = document.getElementById('bottomSheet');
    if (sheet) sheet.classList.remove('open');
}

function filtrarZona(zona, btn) {
    document.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    cerrarDetalle();
    cargarPropiedades(zona);
}

// Cerrar detalle al tocar el mapa libre
map.on('click', cerrarDetalle);

// Iniciar carga al abrir la web
cargarPropiedades();