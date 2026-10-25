<?php

require_once("Config/Config.php");
require_once("Helpers/Helpers.php");

$url = $_GET['url'] ?? 'Home';
$arrUrl = explode("/", $url);

$controller = ucwords($arrUrl[0]) ?? 'Home';
$method = $arrUrl[1] ?? "index";
$params = "";

if (!empty($arrUrl[2])) {
    if ($arrUrl[2] != "") {
        for ($i = 2; $i < count($arrUrl); $i++) {
            $params .= $arrUrl[$i] . ",";
        }
        $params = trim($params, ",");
    }
}

require_once("Libraries/Core/Autoload.php");
require_once("Libraries/Core/Load.php");
