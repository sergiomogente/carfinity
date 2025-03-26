<?php
$host = "localhost"; // Servidor de la base de datos
$user = "root"; // Usuario de MySQL
$password = ""; // Contraseña (déjala vacía si usas XAMPP o MAMP)
$database = "carfinity"; // Nombre de la base de datos

// Crear la conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
