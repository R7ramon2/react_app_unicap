<?php

include "./UnicapApi.php";

if(isset($_GET['matricula'])){
    $api = new UnicapApi();
    $api->getImage($_GET['matricula']);
}
else{
    echo "erro ao recuperar a imagem";
}

?>