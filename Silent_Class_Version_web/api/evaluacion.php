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

// Obtener el evaluacionId desde los parámetros de la URL
$evaluacionId = isset($_GET['evaluacionId']) ? $_GET['evaluacionId'] : null;

if (!$evaluacionId) {
    http_response_code(400);
    echo json_encode(['message' => 'EvaluacionId es requerido']);
    exit();
}

// Consulta SQL para obtener los detalles de la evaluación y el curso asociado
$sql = "
    SELECT e.*, c.nombre AS cursoNombre 
    FROM evaluaciones e 
    JOIN cursos c ON e.curso_id = c.id 
    WHERE e.id = ?
";

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);
$stmt->execute([$evaluacionId]);

// Obtener los resultados
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    http_response_code(404);
    echo json_encode(['message' => 'Evaluación no encontrada']);
    exit();
}

// Devolver el resultado en formato JSON
echo json_encode($result);
?>
