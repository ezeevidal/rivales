<?php
// conexion.php

$servername = "localhost";
$username = "root"; // Por defecto en XAMPP
$password = ""; // Sin contraseña en XAMPP
$database = "rivales";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>