<?php
// header('Content-Type: text/html; charset=iso');
// header('Content-Type: application/json');

$ret_post = $_POST;
$ret_get = $_GET;

// $request = file_get_contents('php://input');
// $json = json_decode($request);

if(isset($_GET['token'])){
    $token = $_GET['token'];
    $opts = array('http' =>
        array(
            'method'  => 'GET',
            'header'  => array ('Content-Type: application/x-www-form-urlencoded','Referer: https://www1.unicap.br/pergamum3/Pergamum/biblioteca_s/meu_pergamum/index.php?flag=index.php','Cookie: '.$token)
        )
    );

    $context  = stream_context_create($opts);
    $result = file_get_contents('https://www1.unicap.br/pergamum3/Pergamum/biblioteca_s/meu_pergamum/emp_renovacao.php', false, $context);

    print_r($http_response_header);
    print_r($result);
}
?>