<?php
class   Conexion
{
    private $conect;

    public function __construct()
    {
        try {
            $this->conect = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
            $this->conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            exit();
        }
    }

    public function connect()
    {
        return $this->conect;
    }
}
