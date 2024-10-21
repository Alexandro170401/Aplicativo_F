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

// Verificar si el cursoId fue pasado como parámetro
if (isset($_GET['cursoId'])) {
    $cursoId = $_GET['cursoId'];
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Falta el ID del curso']);
    exit();
}

// Consulta SQL para obtener las preguntas del curso
$sql = 'SELECT * FROM preguntas WHERE curso_id = ?';

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);
$stmt->execute([$cursoId]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si hay preguntas para ese curso
if (!$result) {
    http_response_code(404); // Not Found
    echo json_encode(['message' => 'No se encontraron preguntas para este curso']);
    exit();
}

// Formatear las preguntas para que las opciones y la respuesta correcta estén bien estructuradas
$preguntasFormateadas = array_map(function($pregunta) {
    return [
        'id' => $pregunta['id'],
        'pregunta' => $pregunta['pregunta'],
        'especialidad' => $pregunta['especialidad'],
        'opciones' => [
            $pregunta['opcion1'], 
            $pregunta['opcion2'], 
            $pregunta['opcion3'], 
            $pregunta['opcion4']
        ],
        'respuestaCorrecta' => $pregunta['respuesta_correcta']
    ];
}, $result);

// Devolver las preguntas formateadas en formato JSON
echo json_encode(['preguntas' => $preguntasFormateadas]);

// Cerrar la conexión
$conn = null;
?>
