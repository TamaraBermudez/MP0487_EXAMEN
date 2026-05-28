<?php
//https://phppot.com/php/php-upload-image-to-database/
//https://www.youtube.com/watch?v=5L9UhOnuos0&t=801s
//Credenciales que usaremos para entrar en la bbdd

$host ="localhost";
$dbname = "mp0487_firalia";
$username = "root"; //IMPORTANTE CAMBIAR , NO QUEREMOS ENTRAR CON ROOT
$password = "";


//Objeto mysqli

$mysqli = new mysqli($host, $username, $password, $dbname);

//Si error, devolver mensaje

if ($mysqli->connect_errno){

    die("Connection error: " . $mysqli->connect_error);

}

return $mysqli;
?>