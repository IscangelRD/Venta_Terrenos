<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Catálogo de Terrenos y Propiedades</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Estilos Propios -->
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

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Lógica de la Aplicación -->
    <script src="public/js/app.js"></script>
</body>
</html>