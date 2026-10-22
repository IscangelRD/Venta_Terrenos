<?php
// models/Terreno.php

class Terreno {
    private $conn;
    private $table = "propiedades";

    public function __construct($db) {
        $this->conn = $db;
    }

   public function obtenerGaleria($propiedad_id) {
        $query = "SELECT nombre_archivo FROM propiedad_imagenes WHERE propiedad_id = :propiedad_id ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':propiedad_id' => $propiedad_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function obtenerTodas($zona = null) {
        $query = "SELECT * FROM " . $this->table . " WHERE estado != 'vendido'";
        if ($zona && $zona !== 'todas') {
            $query .= " AND zona = :zona";
        }
        $query .= " ORDER BY fecha_registro DESC";
        $stmt = $this->conn->prepare($query);
        if ($zona && $zona !== 'todas') {
            $stmt->bindParam(':zona', $zona);
        }
        $stmt->execute();
        $propiedades = $stmt->fetchAll();

        // Adjuntar el array de fotos adicionales a cada propiedad
        foreach ($propiedades as &$prop) {
            $prop['galeria'] = $this->obtenerGaleria($prop['id']);
        }

        return $propiedades;
    }
    // Para el panel de administración (muestra absolutamente todas, incluso vendidas)
    public function obtenerTodasAdmin() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY fecha_registro DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerZonas() {
        $query = "SELECT DISTINCT zona FROM " . $this->table . " WHERE estado != 'vendido' ORDER BY zona ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function crear($datos) {
        $query = "INSERT INTO " . $this->table . " 
                  (titulo, tipo, precio, precio_corto, superficie_m2, zona, latitud, longitud, poligono, estatus_legal, servicios, descripcion, imagen_principal, estado) 
                  VALUES 
                  (:titulo, :tipo, :precio, :precio_corto, :superficie_m2, :zona, :latitud, :longitud, :poligono, :estatus_legal, :servicios, :descripcion, :imagen_principal, :estado)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':titulo'           => $datos['titulo'],
            ':tipo'             => $datos['tipo'],
            ':precio'           => $datos['precio'],
            ':precio_corto'     => $datos['precio_corto'],
            ':superficie_m2'    => $datos['superficie_m2'],
            ':zona'             => $datos['zona'],
            ':latitud'          => $datos['latitud'],
            ':longitud'         => $datos['longitud'],
            ':poligono'         => $datos['poligono'],
            ':estatus_legal'    => $datos['estatus_legal'],
            ':servicios'        => $datos['servicios'],
            ':descripcion'      => $datos['descripcion'],
            ':imagen_principal' => $datos['imagen_principal'],
            ':estado'           => $datos['estado']
        ]);
        return $ejecutado ? $this->conn->lastInsertId() : false;
    }

    // Cambiar estado rápidamente (disponible / apartado / vendido)
    public function cambiarEstado($id, $nuevoEstado) {
        $query = "UPDATE " . $this->table . " SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
    }

    // Eliminar propiedad
    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }



    // Guardar imagen en la galería
    public function agregarImagenGaleria($propiedad_id, $nombre_archivo) {
        $query = "INSERT INTO propiedad_imagenes (propiedad_id, nombre_archivo) VALUES (:propiedad_id, :nombre_archivo)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':propiedad_id'   => $propiedad_id,
            ':nombre_archivo' => $nombre_archivo
        ]);
    }
}
?>