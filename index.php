<?php
// index.php (En la raíz del proyecto)

require_once __DIR__ . '/controllers/TerrenoController.php';

$controller = new TerrenoController();

// Verificamos si se pide una acción por URL (ej. index.php?action=api)
$action = isset($_GET['action']) ? $_GET['action'] : 'home';

switch ($action) {
    case 'api':
        $controller->apiListar();
        break;
    case 'home':
    default:
        $controller->index();
        break;
    case 'admin':
        $controller->admin();
        break;
    case 'guardar':
        $controller->guardar();
        break;
    case 'estado':
        $controller->cambiarEstado();
        break;
    case 'eliminar':
        $controller->eliminar();
        break;
}
