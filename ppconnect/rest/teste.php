<?php
// load object by id
$location = 'http://localhost/ppconnect/rest.php';
$parameters = array();
$parameters['class'] = 'SystemUserService';
$parameters['method'] = 'load';
$parameters['id'] = '1';
$url = $location . '?' . http_build_query($parameters);
var_dump( json_decode( file_get_contents($url) ) );

