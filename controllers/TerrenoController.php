<?php
// controllers/TerrenoController.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Terreno.php';

class TerrenoController {
    private $db;
    private $terrenoModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->terrenoModel = new Terreno($this->db);
    }

    public function index() {
        $zonas = $this->terrenoModel->obtenerZonas();
        require_once __DIR__ . '/../views/home.php';
    }

    public function apiListar() {
        header('Content-Type: application/json; charset=utf-8');
        $zona = isset($_GET['zona']) ? $_GET['zona'] : 'todas';
        $propiedades = $this->terrenoModel->obtenerTodas($zona);
        echo json_encode($propiedades);
    }

    public function admin() {
        $propiedades = $this->terrenoModel->obtenerTodasAdmin();
        require_once __DIR__ . '/../views/admin.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $precio = floatval($_POST['precio']);
            
            if ($precio >= 1000000) {
                $precio_corto = '$' . round($precio / 1000000, 1) . 'M';
            } else {
                $precio_corto = '$' . round($precio / 1000, 0) . 'k';
            }

            $nombreImagen = 'default.jpg';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $directorioImg = __DIR__ . '/../public/img/';
                if (!is_dir($directorioImg)) {
                    mkdir($directorioImg, 0777, true);
                }
                $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $nombreAux = 'prop_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $directorioImg . $nombreAux)) {
                    $nombreImagen = $nombreAux;
                }
            }

            $datos = [
                'titulo'           => $_POST['titulo'],
                'tipo'             => $_POST['tipo'],
                'precio'           => $precio,
                'precio_corto'     => $precio_corto,
                'superficie_m2'    => intval($_POST['superficie_m2']),
                'zona'             => trim($_POST['zona']),
                'latitud'          => $_POST['latitud'],
                'longitud'         => $_POST['longitud'],
                'poligono'         => !empty($_POST['poligono']) ? $_POST['poligono'] : null,
                'estatus_legal'    => $_POST['estatus_legal'],
                'servicios'        => $_POST['servicios'],
                'descripcion'      => $_POST['descripcion'],
                'imagen_principal' => $nombreImagen,
                'estado'           => $_POST['estado']
            ];

            $this->terrenoModel->crear($datos);
            header('Location: index.php?action=admin&ok=1');
            exit;
        }
    }

    public function cambiarEstado() {
        if (isset($_GET['id']) && isset($_GET['estado'])) {
            $this->terrenoModel->cambiarEstado(intval($_GET['id']), $_GET['estado']);
        }
        header('Location: index.php?action=admin');
        exit;
    }

    public function eliminar() {
        if (isset($_GET['id'])) {
            $this->terrenoModel->eliminar(intval($_GET['id']));
        }
        header('Location: index.php?action=admin');
        exit;
    }
}
?>