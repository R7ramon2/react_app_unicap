<?php
// header('Content-Type: text/html; charset=iso');
// header('Content-Type: application/json');

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include "./UnicapApi.php";



$ret_post = $_POST;
$ret_get = $_GET;

$request = file_get_contents('php://input');
$json = json_decode($request);

if(isset($json->matricula) && isset($json->senha)){
    $api = new UnicapApi();
    $api->login($json->matricula,$json->senha);
}

?>
