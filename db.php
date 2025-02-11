<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";  // Si tienes contraseña en MySQL, escríbela aquí
    private $dbname = "formulario_web"; // Asegúrate de que este nombre es correcto
    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
    }
}
?>