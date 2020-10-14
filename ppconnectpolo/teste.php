<?php
session_start();
//if(isset($_POST['img'])){

    // update an new object
    $location = 'http://localhost/ppconnectpolo/rest.php';
    $parameters = array();
    $parameters['class'] = 'QuestoesDasProvasGeradasService';
    $parameters['method'] = 'store';
    $parameters['data'] = ['id' =>  $_SESSION['questao_resp'], 'imagem' =>  'tessfgggtando' ];
    $url = $location . '?' . http_build_query($parameters);
//}

?>