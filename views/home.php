<!DOCTYPE html>
<html lang="es">
<head>
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <?php
        // Detectar dominio y protocolo automáticamente
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $baseUrl = $protocol . $_SERVER['HTTP_HOST'] . strtok($_SERVER["REQUEST_URI"], '?');

        if (!empty($loteCompartido)) {
            $ogTitulo = htmlspecialchars($loteCompartido['titulo']) . " en " . htmlspecialchars($loteCompartido['zona']) . " - $" . number_format($loteCompartido['precio'], 0) . " MXN";
            $ogDesc = "Superficie: " . $loteCompartido['superficie_m2'] . " m² | Estatus: " . $loteCompartido['estatus_legal'] . " | Servicios: " . $loteCompartido['servicios'] . ". Toca para ver ubicación satelital y linderos exactos.";
            $foto = (!empty($loteCompartido['imagen_principal']) && $loteCompartido['imagen_principal'] !== 'default.jpg') 
                    ? $loteCompartido['imagen_principal'] 
                    : 'default.jpg';
            $ogImg = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/public/img/" . $foto;
            $ogUrl = $baseUrl . "?lote=" . $loteCompartido['id'];
        } else {
            $ogTitulo = "Catálogo de Terrenos y Lotes | Ubicación Satelital";
            $ogDesc = "Explora lotes disponibles con vista satelital interactiva, linderos exactos y trato directo por WhatsApp.";
            $ogImg = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/public/img/default.jpg";
            $ogUrl = $baseUrl;
        }
    ?>

    <title><?= $ogTitulo ?></title>

    <!-- Metaetiquetas Open Graph para Facebook, WhatsApp y Redes -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= $ogTitulo ?>" />
    <meta property="og:description" content="<?= $ogDesc ?>" />
    <meta property="og:image" content="<?= $ogImg ?>" />
    <meta property="og:url" content="<?= $ogUrl ?>" />
    <meta name="twitter:card" content="summary_large_image">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>

    <!-- Barra Superior con Filtros Dinámicos desde MySQL -->
    <div class="top-bar">
        <button class="filter-chip active" onclick="filtrarZona('todas', this)">📍 Todas las zonas</button>
        <?php foreach ($zonas as $zona): ?>
            <button class="filter-chip" onclick="filtrarZona('<?= htmlspecialchars($zona) ?>', this)">
                <?= htmlspecialchars($zona) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Mapa Interactivo -->
    <div id="map"></div>

    <!-- Carrusel Inferior de Tarjetas -->
    <div class="carousel-container" id="carousel"></div>

    <!-- Bottom Sheet (Ficha Técnica y Conversión) -->
    <div class="bottom-sheet" id="bottomSheet">
        <div class="sheet-handle" onclick="cerrarDetalle()"></div>
        <div id="sheetContent"></div>
    </div>
    <!-- Visor de Imágenes a Pantalla Completa (Lightbox con Zoom) -->
    <div id="lightboxModal" class="lightbox-modal" onclick="cerrarLightbox(event)">
        <div class="lightbox-toolbar">
            <span id="lightboxCounter" class="lightbox-counter">1 / 1</span>
            <div style="display:flex; gap:10px;">
                <button type="button" class="lightbox-btn" onclick="alternarZoom(event)">🔍 Zoom</button>
                <button type="button" class="lightbox-btn" onclick="cerrarLightbox(event, true)">✕</button>
            </div>
        </div>
        <button type="button" class="lightbox-nav prev" onclick="cambiarFotoLightbox(-1, event)">‹</button>
        <div class="lightbox-img-wrapper" id="lightboxWrapper">
            <img id="lightboxImg" src="" alt="Vista ampliada" onclick="alternarZoom(event)">
        </div>
        <button type="button" class="lightbox-nav next" onclick="cambiarFotoLightbox(1, event)">›</button>
    </div>

            <!-- Pasar a JS el lote compartido si existe -->
    <script>
        const LOTE_INICIAL = <?= !empty($loteCompartido) ? json_encode($loteCompartido['id']) : 'null' ?>;
    </script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Lógica de la Aplicación -->
    <script src="public/js/app.js"></script>
</body>
</html>