<?php

spl_autoload_register(function($className) {
    $filePath = "Libraries/Core/{$className}.php";
    if( file_exists($filePath) ) {
        require_once $filePath;
    }
});