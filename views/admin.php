<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Gestión de Terrenos</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { margin: 0; background: #f1f5f9; color: #0f172a; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.1fr; gap: 24px; }
        @media (max-width: 900px) { .container { grid-template-columns: 1fr; } }
        .card { background: #fff; padding: 20px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        h2 { margin-top: 0; color: #16a34a; font-size: 1.25rem; }
        .form-group { margin-bottom: 12px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        label { display: block; font-size: 0.84rem; font-weight: 600; margin-bottom: 4px; color: #334155; }
        input, select, textarea { width: 100%; padding: 9px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; }
        #adminMap { height: 350px; width: 100%; border-radius: 12px; margin-bottom: 8px; border: 2px solid #16a34a; }
        .map-tools { display: flex; gap: 8px; margin-bottom: 12px; }
        .btn-tool { background: #e2e8f0; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; }
        .btn-tool.active { background: #0f172a; color: #fff; }
        .btn-submit { background: #16a34a; color: #fff; border: none; padding: 14px; width: 100%; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer; }
        .top-links { max-width: 1200px; margin: 0 auto 16px auto; display: flex; justify-content: space-between; align-items: center; }
        .btn-ver { background: #0f172a; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; }
        .alert-ok { background: #dcfce7; color: #15803d; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.85rem; }
        th, td { text-align: left; padding: 10px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .badge-est { padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 0.72rem; display: inline-block; }
        .est-disponible { background: #dcfce7; color: #15803d; }
        .est-apartado { background: #fef9c3; color: #a16207; }
        .est-vendido { background: #fee2e2; color: #b91c1c; }
        .btn-sm { text-decoration: none; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; margin-right: 4px; display: inline-block; }
    </style>
</head>
<body>

<div class="top-links">
    <h1>⚙️ Administración de Terrenos</h1>
    <a href="index.php" class="btn-ver" target="_blank">🗺️ Ver Mapa Público</a>
</div>

<div class="container">
    <!-- Formulario de Alta -->
    <div class="card">
        <h2>➕ Registrar Nueva Propiedad</h2>
        <?php if (isset($_GET['ok'])): ?>
            <div class="alert-ok">✅ Propiedad guardada y publicada correctamente.</div>
        <?php endif; ?>

        <form action="index.php?action=guardar" method="POST" enctype="multipart/form-data">
            <label>1. Ubica el terreno y dibuja sus esquinas (opcional):</label>
            <div class="map-tools">
                <button type="button" class="btn-tool active" id="btnModoPin" onclick="cambiarModo('pin')">📍 1. Poner Pin Central</button>
                <button type="button" class="btn-tool" id="btnModoPoli" onclick="cambiarModo('poligono')">📐 2. Trazar Linderos (Polígono)</button>
                <button type="button" class="btn-tool" onclick="limpiarPoligono()">🗑️ Borrar Trazo</button>
            </div>
            <div id="adminMap"></div>

            <div class="form-row">
                <div class="form-group">
                    <label>Latitud</label>
                    <input type="text" name="latitud" id="lat" required readonly placeholder="Clic en el mapa...">
                </div>
                <div class="form-group">
                    <label>Longitud</label>
                    <input type="text" name="longitud" id="lng" required readonly placeholder="Clic en el mapa...">
                </div>
            </div>
            <input type="hidden" name="poligono" id="poligonoInput">

            <div class="form-group">
                <label>Título comercial</label>
                <input type="text" name="titulo" required placeholder="Ej. Lote Campestre El Comal">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tipo</label>
                    <select name="tipo">
                        <option value="terreno">Terreno</option>
                        <option value="casa">Casa</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Estado inicial</label>
                    <select name="estado">
                        <option value="disponible">Disponible</option>
                        <option value="apartado">Apartado</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Precio Total (MXN)</label>
                    <input type="number" step="0.01" name="precio" required placeholder="Ej. 300000">
                </div>
                <div class="form-group">
                    <label>Superficie (m²)</label>
                    <input type="number" name="superficie_m2" required placeholder="Ej. 500">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Zona / Municipio</label>
                    <input type="text" name="zona" required placeholder="Ej. Metepec, Zona Norte...">
                </div>
                <div class="form-group">
                    <label>Estatus Legal</label>
                    <input type="text" name="estatus_legal" required placeholder="Ej. Escritura Pública">
                </div>
            </div>

            <div class="form-group">
                <label>Servicios disponibles</label>
                <input type="text" name="servicios" required placeholder="Ej. Agua y Luz a pie de lote">
            </div>

            <div class="form-group">
                <label>Fotografía de Portada (Principal)</label>
                <input type="file" name="imagen" accept="image/*">
            </div>

            <div class="form-group">
                <label>📸 Galería de Fotos Adicionales (Puedes seleccionar varias)</label>
                <input type="file" name="galeria[]" accept="image/*" multiple>
            </div>

            <div class="form-group">
                <label>Descripción detallada</label>
                <textarea name="descripcion" rows="3" required placeholder="Describe accesos, topografía, cercanías y facilidades de pago..."></textarea>
            </div>

            <button type="submit" class="btn-submit">💾 Guardar y Publicar en el Mapa</button>
        </form>
    </div>

    <!-- Inventario y Cambio Rápido de Estado -->
    <div class="card">


    <!-- Cotizador Administrativo de Terrenos -->
        <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:12px; padding:16px; margin-bottom:24px;">
            <h2 style="margin-bottom:12px; color:#0f172a;">🧮 Cotizador Administrativo de Financiamiento</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Seleccionar Propiedad</label>
                    <select id="cotPropiedad" onchange="cargarPrecioCotizador()">
                        <?php 
                        $hayCotizables = false;
                        foreach ($propiedades as $p): 
                            if ($p['estado'] === 'vendido') continue; // Se ocultan los vendidos
                            $hayCotizables = true;
                        ?>
                            <option value="<?= $p['precio'] ?>" data-titulo="<?= htmlspecialchars($p['titulo']) ?>" data-sup="<?= $p['superficie_m2'] ?>" data-zona="<?= htmlspecialchars($p['zona']) ?>">
                                <?= htmlspecialchars($p['titulo']) ?> (<?= strtoupper($p['estado']) ?>) - $<?= number_format($p['precio'], 0) ?>
                            </option>
                        <?php endforeach; ?>

                        <?php if (!$hayCotizables): ?>
                            <option value="0">No hay propiedades activas para cotizar</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Precio Base (MXN)</label>
                    <input type="number" id="cotPrecio" oninput="calcularCotizacionAdmin()" placeholder="Precio">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Enganche Acordado ($)</label>
                    <input type="number" id="cotEnganche" step="5000" oninput="calcularCotizacionAdmin()">
                </div>
                <div class="form-group">
                    <label>Plazo (Meses)</label>
                    <select id="cotMeses" onchange="calcularCotizacionAdmin()">
                        <option value="0">Contado (1 exhibición)</option>
                        <option value="6">6 Meses</option>
                        <option value="12">12 Meses (1 año)</option>
                        <option value="18">18 Meses</option>
                        <option value="24" selected>24 Meses (2 años)</option>
                        <option value="36">36 Meses (3 años)</option>
                        <option value="48">48 Meses (4 años)</option>
                    </select>
                </div>
            </div>

            <div id="resumenCotizacion" style="background:#dcfce7; color:#15803d; padding:12px; border-radius:8px; font-size:0.9rem; margin-bottom:12px; line-height:1.5;">
                Selecciona una propiedad para calcular.
            </div>

            <div class="form-row">
                <input type="tel" id="cotTelCliente" placeholder="WhatsApp del cliente (10 dígitos)">
                <button type="button" onclick="enviarCotizacionCliente()" style="background:#25D366; color:#fff; border:none; border-radius:8px; font-weight:700; cursor:pointer; padding:10px;">
                    📲 Enviar Cotización al Cliente
                </button>
            </div>
        </div>

        <h2>📋 Inventario de Propiedades (<?= count($propiedades) ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>Propiedad</th>
                    <th>Precio</th>
                    <th>Estado Actual</th>
                    <th>Cambiar Estado / Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($propiedades as $p): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($p['titulo']) ?></strong><br>
                        <small style="color:#64748b;">📍 <?= htmlspecialchars($p['zona']) ?> • <?= $p['superficie_m2'] ?> m²</small>
                    </td>
                    <td><strong>$<?= number_format($p['precio'], 0) ?></strong></td>
                    <td>
                        <span class="badge-est est-<?= $p['estado'] ?>">
                            <?= strtoupper($p['estado']) ?>
                        </span>
                    </td>
                    <td>
                        <select onchange="if(this.value) window.location.href=this.value" style="width:auto; padding:4px; font-size:0.8rem;">
                            <option value="">Cambiar a...</option>
                            <option value="index.php?action=estado&id=<?= $p['id'] ?>&estado=disponible">🟢 Disponible</option>
                            <option value="index.php?action=estado&id=<?= $p['id'] ?>&estado=apartado">🟡 Apartado</option>
                            <option value="index.php?action=estado&id=<?= $p['id'] ?>&estado=vendido">🔴 Vendido (Ocultar)</option>
                        </select>
                        <a href="index.php?action=eliminar&id=<?= $p['id'] ?>" 
                           onclick="return confirm('¿Seguro que deseas eliminar esta propiedad?')" 
                           class="btn-sm" style="background:#fee2e2; color:#b91c1c; margin-left:4px;">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>



<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const calles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
    const satelite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}');

    const adminMap = L.map('adminMap', {
        center: [20.1000, -98.7300],
        zoom: 12,
        layers: [satelite]
    });

    L.control.layers({ "🛰️ Satélite": satelite, "🗺️ Calles": calles }).addTo(adminMap);

    let modoActual = 'pin';
    let pinSeleccionado = null;
    let puntosPoligono = [];
    let figuraPoligono = null;

    function cambiarModo(modo) {
        modoActual = modo;
        document.getElementById('btnModoPin').classList.toggle('active', modo === 'pin');
        document.getElementById('btnModoPoli').classList.toggle('active', modo === 'poligono');
    }

    function limpiarPoligono() {
        puntosPoligono = [];
        document.getElementById('poligonoInput').value = '';
        if (figuraPoligono) {
            adminMap.removeLayer(figuraPoligono);
            figuraPoligono = null;
        }
    }

    adminMap.on('click', function(e) {
        if (modoActual === 'pin') {
            const lat = e.latlng.lat.toFixed(8);
            const lng = e.latlng.lng.toFixed(8);
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;

            if (pinSeleccionado) {
                pinSeleccionado.setLatLng(e.latlng);
            } else {
                pinSeleccionado = L.marker(e.latlng).addTo(adminMap);
            }
        } else {
            // Modo trazar polígono del terreno
            puntosPoligono.push([parseFloat(e.latlng.lat.toFixed(8)), parseFloat(e.latlng.lng.toFixed(8))]);
            document.getElementById('poligonoInput').value = JSON.stringify(puntosPoligono);

            if (figuraPoligono) {
                figuraPoligono.setLatLngs(puntosPoligono);
            } else {
                figuraPoligono = L.polygon(puntosPoligono, { color: '#22c55e', weight: 3, fillOpacity: 0.35 }).addTo(adminMap);
            }
        }
    });


    // Lógica del Cotizador Administrativo
    function cargarPrecioCotizador() {
        const select = document.getElementById('cotPropiedad');
        if (!select || select.options.length === 0) return;

        const opt = select.options[select.selectedIndex];
        const precio = parseFloat(select.value) || 0;

        document.getElementById('cotPrecio').value = precio > 0 ? precio : '';
        document.getElementById('cotEnganche').value = precio > 0 ? Math.round(precio * 0.20) : '';

        if (precio === 0) {
            document.getElementById('resumenCotizacion').innerHTML = 'No hay terrenos disponibles para cotizar.';
            return;
        }

        calcularCotizacionAdmin();
    }

    function calcularCotizacionAdmin() {
        const precio = parseFloat(document.getElementById('cotPrecio').value) || 0;
        const enganche = parseFloat(document.getElementById('cotEnganche').value) || 0;
        const meses = parseInt(document.getElementById('cotMeses').value) || 0;
        const saldo = Math.max(0, precio - enganche);
        const mensualidad = meses > 0 ? Math.round(saldo / meses) : 0;

        const caja = document.getElementById('resumenCotizacion');
        if (meses === 0) {
            caja.innerHTML = `<strong>💰 Pago de Contado:</strong> $${precio.toLocaleString('es-MX')} MXN`;
        } else {
            caja.innerHTML = `
                <strong>Precio Total:</strong> $${precio.toLocaleString('es-MX')} MXN | 
                <strong>Enganche:</strong> $${enganche.toLocaleString('es-MX')} MXN<br>
                <strong>Saldo a financiar:</strong> $${saldo.toLocaleString('es-MX')} MXN 
                👉 <strong>${meses} mensualidades de $${mensualidad.toLocaleString('es-MX')} MXN</strong>
            `;
        }
    }

    function enviarCotizacionCliente() {
        const select = document.getElementById('cotPropiedad');
        const opt = select.options[select.selectedIndex];
        const titulo = opt ? opt.dataset.titulo : 'Terreno';
        const sup = opt ? opt.dataset.sup : '';
        const zona = opt ? opt.dataset.zona : '';

        const precio = parseFloat(document.getElementById('cotPrecio').value) || 0;
        const enganche = parseFloat(document.getElementById('cotEnganche').value) || 0;
        const meses = parseInt(document.getElementById('cotMeses').value) || 0;
        const saldo = Math.max(0, precio - enganche);
        const mensualidad = meses > 0 ? Math.round(saldo / meses) : 0;

        const tel = document.getElementById('cotTelCliente').value.replace(/[^0-9]/g, '');

        let textoPlan = meses === 0 
            ? `• *Pago de Contado:* $${precio.toLocaleString('es-MX')} MXN`
            : `• *Precio Total:* $${precio.toLocaleString('es-MX')} MXN\n• *Enganche:* $${enganche.toLocaleString('es-MX')} MXN\n• *Financiamiento:* ${meses} mensualidades de *$${mensualidad.toLocaleString('es-MX')} MXN*`;

        const msg = encodeURIComponent(
            `📄 *COTIZACIÓN DE PROPIEDAD*\n\n` +
            `📍 *${titulo}* (${zona} - ${sup} m²)\n` +
            `${textoPlan}\n\n` +
            `Quedo a tus órdenes para cualquier duda o para agendar tu visita al terreno.`
        );

        const url = tel.length >= 10 ? `https://wa.me/521${tel}?text=${msg}` : `https://wa.me/?text=${msg}`;
        window.open(url, '_blank');
    }

    // Inicializar al abrir el panel
    cargarPrecioCotizador();
</script>
</body>
</html>