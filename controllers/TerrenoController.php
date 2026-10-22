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

    // En controllers/TerrenoController.php dentro de guardar():
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $precio = floatval($_POST['precio']);
            
            if ($precio >= 1000000) {
                $precio_corto = '$' . round($precio / 1000000, 1) . 'M';
            } else {
                $precio_corto = '$' . round($precio / 1000, 0) . 'k';
            }

            $directorioImg = __DIR__ . '/../public/img/';
            if (!is_dir($directorioImg)) {
                mkdir($directorioImg, 0777, true);
            }

            // 1. Imagen Principal
            $nombreImagen = 'default.jpg';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $nombreAux = 'prop_' . time() . '_' . rand(100, 999) . '.' . $ext;
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

            // Guardamos la propiedad y recibimos su ID
            $propiedadId = $this->terrenoModel->crear($datos);

            // 2. Subida de la Galería de Fotos Adicionales
            if ($propiedadId && isset($_FILES['galeria'])) {
                $totalArchivos = count($_FILES['galeria']['name']);
                for ($i = 0; $i < $totalArchivos; $i++) {
                    if ($_FILES['galeria']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['galeria']['name'][$i], PATHINFO_EXTENSION));
                        $nombreFoto = 'gal_' . $propiedadId . '_' . time() . '_' . $i . '.' . $ext;
                        if (move_uploaded_file($_FILES['galeria']['tmp_name'][$i], $directorioImg . $nombreFoto)) {
                            $this->terrenoModel->agregarImagenGaleria($propiedadId, $nombreFoto);
                        }
                    }
                }
            }

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