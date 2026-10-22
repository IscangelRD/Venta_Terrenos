// public/js/app.js

// Configura tu número de WhatsApp (con lada de país)
const TELEFONO_WHATSAPP = "5217716837879";

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

// Inicializar Mapa centrado en Hidalgo / Zona central
const map = L.map('map', {
    zoomControl: false,
    layers: [capaSatelite]
}).setView([20.1000, -98.7300], 12);

L.control.layers({ "🛰️ Satélite": capaSatelite, "🗺️ Mapa": capaCalles }, null, { position: 'topright' }).addTo(map);

// Cargar propiedades desde el Controlador PHP
async function cargarPropiedades(zona = 'todas') {
    try {
        const res = await fetch(`index.php?action=api&zona=${encodeURIComponent(zona)}`);
        propiedades = await res.json();
        renderizarPropiedades(propiedades);
    } catch (error) {
        console.error("Error al cargar propiedades:", error);
    }
}

function renderizarPropiedades(lista) {
    if (!carousel) return;
    carousel.innerHTML = '';

    // Limpiar pines y polígonos anteriores
    Object.values(markers).forEach(m => map.removeLayer(m));
    markers = {};

    Object.values(polygons).forEach(p => map.removeLayer(p));
    polygons = {};

    if (!lista || lista.length === 0) {
        carousel.innerHTML = '<div class="property-card">Sin propiedades disponibles en esta zona.</div>';
        return;
    }

    lista.forEach((prop, index) => {
        const lat = parseFloat(prop.latitud);
        const lng = parseFloat(prop.longitud);
        const precioNum = parseFloat(prop.precio);

        // Pin de precio en el mapa
        const claseEstado = prop.estado === 'apartado' ? 'apartado' : '';
        const icon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div class="price-pin ${claseEstado}" id="pin-${prop.id}">${prop.precio_corto}</div>`,
            iconSize: [64, 28],
            iconAnchor: [32, 14]
        });

        const marker = L.marker([lat, lng], { icon }).addTo(map);
        marker.on('click', (e) => {
            L.DomEvent.stopPropagation(e);
            centrarPropiedad(prop.id);
            abrirDetalle(prop);
        });
        markers[prop.id] = marker;

        // Dibujar polígono si lo tiene
        if (prop.poligono) {
            try {
                const coords = JSON.parse(prop.poligono);
                if (Array.isArray(coords) && coords.length >= 3) {
                    const colorPoli = prop.estado === 'apartado' ? '#eab308' : '#22c55e';
                    const poly = L.polygon(coords, {
                        color: colorPoli,
                        weight: 2,
                        fillColor: colorPoli,
                        fillOpacity: 0.3
                    }).addTo(map);
                    polygons[prop.id] = poly;
                }
            } catch (err) {}
        }

        // Tarjeta del carrusel
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

// Sincronizar carrusel táctil con el mapa
let scrollTimeout;
if (carousel) {
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
}

function abrirDetalle(prop) {
    const sheet = document.getElementById('bottomSheet');
    const content = document.getElementById('sheetContent');
    if (!sheet || !content) return;

    const precioTotal = parseFloat(prop.precio);
    const precioFormateado = precioTotal.toLocaleString('es-MX');

    // Preparar fotos (portada + galería)
    let todasLasFotos = [];
    if (prop.imagen_principal && prop.imagen_principal !== 'default.jpg') {
        todasLasFotos.push(prop.imagen_principal);
    }
    if (Array.isArray(prop.galeria) && prop.galeria.length > 0) {
        todasLasFotos = todasLasFotos.concat(prop.galeria);
    }

    let sliderHtml = '';
    if (todasLasFotos.length > 0) {
        sliderHtml = `
            <div style="display:flex; gap:8px; overflow-x:auto; scroll-snap-type:x mandatory; margin-bottom:12px; border-radius:14px; scrollbar-width:none;">
                ${todasLasFotos.map((foto, idx) => `
                    <div style="flex:0 0 100%; scroll-snap-align:center; position:relative;">
                        <img src="public/img/${foto}" onerror="this.parentElement.style.display='none'" 
                             style="width:100%; height:200px; object-fit:cover; border-radius:14px; display:block;">
                        ${todasLasFotos.length > 1 ? `
                            <span style="position:absolute; bottom:8px; right:8px; background:rgba(15,23,42,0.75); color:#fff; font-size:0.75rem; padding:3px 8px; border-radius:12px; font-weight:600;">
                                📷 ${idx + 1}/${todasLasFotos.length}
                            </span>
                        ` : ''}
                    </div>
                `).join('')}
            </div>
        `;
    }

    const urlGoogleMaps = `https://www.google.com/maps/search/?api=1&query=${prop.latitud},${prop.longitud}`;

    content.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <span class="card-badge">${prop.tipo.toUpperCase()} • ${prop.estado.toUpperCase()}</span>
            <button onclick="cerrarDetalle()" style="background:#f1f5f9; border:none; border-radius:50%; width:32px; height:32px; font-weight:bold; cursor:pointer;">✕</button>
        </div>
        ${sliderHtml}
        <h2 style="margin: 4px 0; color:#0f172a; font-size:1.25rem;">${prop.titulo}</h2>
        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:10px;">
            <h3 style="color:#16a34a; font-size:1.3rem;">
                $${precioFormateado} MXN 
                <small style="color:#64748b; font-weight:normal; font-size:0.85rem;">(${prop.superficie_m2} m²)</small>
            </h3>
            <a href="${urlGoogleMaps}" target="_blank" style="font-size:0.82rem; color:#2563eb; text-decoration:none; font-weight:600;">📍 Cómo llegar (GPS)</a>
        </div>

        <p style="color:#334155; line-height:1.5; font-size:0.92rem;">${prop.descripcion}</p>

        <div class="trust-box">
            <strong>🛡️ Certeza Legal:</strong> ${prop.estatus_legal}<br>
            <strong>⚡ Servicios:</strong> ${prop.servicios}<br>
            <strong>📍 Ubicación:</strong> ${prop.zona}
        </div>

        <div style="border-top:1px solid #e2e8f0; padding-top:12px; margin-top:12px;">
            <div style="font-size:0.82rem; font-weight:600; color:#334155; margin-bottom:8px;">📲 Déjanos tus datos para enviarte ubicación exacta y planes de pago:</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px;">
            </div>
            <button onclick="enviarLeadWhatsApp(${prop.id}, '${prop.titulo.replace(/'/g, "\\'")}', '${prop.zona.replace(/'/g, "\\'")}', ${precioTotal})" 
                    class="btn-whatsapp" style="border:none; cursor:pointer; margin-top:4px;">
                💬 Pedir Información o Agendar Visita
            </button>
        </div>
    `;
    sheet.classList.add('open');
}

function cerrarDetalle() {
    const sheet = document.getElementById('bottomSheet');
    if (sheet) sheet.classList.remove('open');
}

function filtrarZona(zona, btn) {
    document.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    cerrarDetalle();
    cargarPropiedades(zona);
}

async function enviarLeadWhatsApp(idProp, titulo, zona, precioTotal) {
    const nombre = document.getElementById('leadNombre').value.trim() || 'Interesado';
    const telefono = document.getElementById('leadTelefono').value.trim() || 'Sin especificar';

    try {
        await fetch('index.php?action=lead', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                propiedad_id: idProp,
                nombre: nombre,
                telefono: telefono,
                plan_interes: 'Solicitó informes / planes de pago'
            })
        });
    } catch (e) {
        console.error(e);
    }

    const mensaje = encodeURIComponent(
        `Hola, soy *${nombre}*. Vi en el catálogo el terreno *${titulo}* en *${zona}* ($${precioTotal.toLocaleString('es-MX')} MXN) y me gustaría conocer los planes de pago o agendar una visita.`
    );
    window.open(`https://wa.me/${TELEFONO_WHATSAPP}?text=${mensaje}`, '_blank');
}

// Cerrar detalle al hacer clic en área libre del mapa
map.on('click', cerrarDetalle);

// Inicializar la carga al entrar a la página
cargarPropiedades();