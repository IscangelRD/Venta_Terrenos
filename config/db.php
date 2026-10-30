<?php
// config/db.php

class Database {
    private $host = "localhost";
    private $db_name = "db_terrenos";
    private $username = "root";
    private $password = ""; // En XAMPP por defecto va vacío
    //private $db_name = "u176240576_terrenos";
    //private $username = "u176240576_AARD_Terrenos";
    //private $password = "Angelalonso21"; // En XAMPP por defecto va vacío
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            // Activar reporte de errores y modo de obtención asociativo
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
