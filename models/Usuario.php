<?php
// models/Usuario.php

class Usuario {
    private $conn;
    private $table = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($usuario, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':usuario' => $usuario]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        // 1. Si coincide por texto directo 'admin123', se encripta automáticamente a BCRYPT para seguridad
        if ($user['password'] === $password) {
            $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $this->conn->prepare("UPDATE " . $this->table . " SET password = :p WHERE id = :id");
            $upd->execute([':p' => $nuevoHash, ':id' => $user['id']]);
            return $user;
        }

        // 2. Si ya está encriptada, se valida con el hash
        if (password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}
?>