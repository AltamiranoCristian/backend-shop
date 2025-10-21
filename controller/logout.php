<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
session_start(); //Le avisa al servidor que va a utilizar sesiones
session_destroy();
$json = '{
    "success": true,
    "status": "ok",
    "data": "Sesión cerrada correctamente"
}';
header('Content-type: application/json');
echo $json;