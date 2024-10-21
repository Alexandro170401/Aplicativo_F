<?php
// db.php - Archivo de conexión a la base de datos

$host = 'localhost'; // Cambia según tu servidor
$db = 'emprodig_silentclass'; // Nombre de tu base de datos
$user = 'emprodig_adminsilentclass'; // Tu usuario de la base de datos
$password = 'silentclass123'; // Tu contraseña de la base de datos

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit();
}
?>