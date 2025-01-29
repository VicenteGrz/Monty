<?php
$servername = "localhost";
$username = "u814339862_montyplusadmin";
$password = "Stafatima104!";
$dbname = "u814339862_montyplus";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}