<?php

class HomeController extends Controller{

    public function index() {
        $this->views->render($this, "index");
    }

    public function datos($params) {
        $arr["titulo"] = "Parametros recibidos.";
        $arr["subtitulo"] = "Estos son los parametros recibidos en el metodo datos";
        $arr["params"] = $params;
        $this->views->render($this, "datos", $arr);
    }
}