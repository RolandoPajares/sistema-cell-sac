<?php

class UsuariosController extends Controller
{

    public function index()
    {
        $this->listar();
    }

    public function listar()
    {
        $datos = $this->model->getUsuarios();
        $this->views->render($this, "listar", $datos);
    }

    public function ver($id)
    {
        echo "hola!!";
    }
}
