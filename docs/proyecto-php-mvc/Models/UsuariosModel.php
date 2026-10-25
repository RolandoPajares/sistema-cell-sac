<?php

class UsuariosModel extends Model
{
    private $conexion;

    public function __construct()
    {
        /*try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
            $this->conexion = new \PDO($dsn, DB_USER, DB_PASS);
            $this->conexion->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw $e;
        }*/

        parent::__construct();
        $this->table = "usuarios";
    }

    /*public function getUsuarios()
    {
        $sql = "SELECT * FROM usuarios WHERE estado = 1";
        try {
            if ($this->conexion == null) {
                throw new Exception("Conexión vacía.");
            }
            $stmt = $this->conexion->prepare($sql);
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw $e;
        }
    }*/
}
