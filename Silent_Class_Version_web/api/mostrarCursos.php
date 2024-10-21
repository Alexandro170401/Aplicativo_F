<?php
// Permitir solicitudes desde cualquier origen y permitir los métodos necesarios
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Conexión a la base de datos
require 'db.php';

// Consulta SQL para obtener todos los cursos
$sql = 'SELECT * FROM cursos';

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Obtener los resultados
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los cursos en formato JSON
    echo json_encode($cursos);
    
} catch (PDOException $e) {
    // Manejo de errores
    http_response_code(500);
    echo json_encode(['message' => 'Error al obtener los cursos', 'error' => $e->getMessage()]);
}

// Cerrar la conexión a la base de datos
$conn = null;
?>