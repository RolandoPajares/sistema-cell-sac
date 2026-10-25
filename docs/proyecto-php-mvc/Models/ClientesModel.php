<?php

class ClientesModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = "cliente";
        $this->joins = ["JOIN usuarios U", "ON U.id = cliente.id_usuario"];
    }
}
