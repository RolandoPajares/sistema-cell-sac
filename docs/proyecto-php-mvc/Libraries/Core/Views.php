<?php

class Views {

    public function render($controller, $view, $data = []) {
        $controller = str_replace("Controller", "" , get_class($controller));
        $viewPath = "Views/{$controller}/{$view}.php";
        if( file_exists($viewPath) ) {
            require_once $viewPath;
        }
    }

}