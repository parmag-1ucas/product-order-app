<?php
$host = 'localhost';
$dbname = 'sistemavendas';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $dbname);
if($mysqli->connect_errno){
    echo "Falha ao se conectar: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}