<?php


$host = '127.0.0.1';
$user = 'root';
$pass = 'root'; 
$db   = 'apsti';


$conexion = @new mysqli($host, $user, $pass, $db);


if ($conexion->connect_errno) {
    $pass = 'root';
    $conexion = @new mysqli($host, $user, $pass, $db);
}


if ($conexion->connect_errno) {
    die('Error de conexión a la base de datos: ' . $conexion->connect_error);
}

$conexion->set_charset('utf8mb4');