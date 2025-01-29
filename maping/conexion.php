<?php
$servername = "localhost";
$username = "u814339862_mirutaadmin";
$password = "Stafatima104!";
$dbname = "u814339862_miruta";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Error al conectarse a la base de datos: " . $e->getMessage();
}
?>
