<?php
// Incluir cabeceras para permitir CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Verificar si la solicitud es de tipo OPTIONS y finalizar la ejecución
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'db.php'; // Incluir el archivo de conexión a la base de datos

// Obtener los parámetros de la URL
$especialidad = isset($_GET['especialidad']) ? $_GET['especialidad'] : null;
$cursoActual = isset($_GET['cursoActual']) ? $_GET['cursoActual'] : null;

if (!$especialidad || !$cursoActual) {
    http_response_code(400);
    echo json_encode(['message' => 'Se requieren los parámetros especialidad y cursoActual']);
    exit();
}

// Consulta SQL para seleccionar un curso aleatorio de la misma especialidad que no sea el curso actual
$sql = "
    SELECT * FROM cursos 
    WHERE especialidad = ? AND nombre != ? 
    ORDER BY RAND() 
    LIMIT 1
";

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);
$stmt->execute([$especialidad, $cursoActual]);

// Obtener el resultado
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    http_response_code(404);
    echo json_encode(['message' => 'No se encontró recomendación.']);
    exit();
}

// Devolver el curso recomendado en formato JSON
echo json_encode($result);
?>
