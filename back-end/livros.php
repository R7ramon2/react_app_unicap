<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
header('Content-Type: text/html; charset=iso');
header('Content-Type: application/json');

include "./UnicapApi.php";

if(isset($_GET['token'])){
    $api = new UnicapApi();
    $retorno = $api->getLivros($_GET['token']);
    echo $retorno;
}
?>