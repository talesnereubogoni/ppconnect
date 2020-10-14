<?php
//var_dump($_POST);
session_start();
//if(isset($_POST['img'])){
    // update an new object
    $location = 'http://localhost/ppconnectpolo/rest.php';
    $parameters = array();
    $parameters['class'] = 'QuestoesDasProvasGeradasService';
    $parameters['method'] = 'store';
    $parameters['data'] = ['id' => 37, 'imagem' =>  $_POST['img'] ];
    $url = $location . '?' . http_build_query($parameters);
   // var_dump( json_decode( file_get_contents($url) ) );
//}

?>