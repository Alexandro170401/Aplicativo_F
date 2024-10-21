<?php
// Permitir solicitudes desde cualquier origen y permitir los m¨¦todos necesarios
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Conexi¨®n a la base de datos
require 'db.php';

// Verificar que el m¨¦todo de solicitud sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // M¨¦todo no permitido
    echo json_encode(['message' => 'M¨¦todo no permitido']);
    exit();
}

// Obtener el curso ID de la URL (ejemplo: /api/courses/1)
if (isset($_GET['id'])) {
    $cursoId = $_GET['id'];
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Falta el ID del curso']);
    exit();
}

// Consultas SQL
$sqlCurso = 'SELECT * FROM cursos WHERE id = ?';
$sqlPreguntas = 'SELECT * FROM preguntas WHERE curso_id = ?';

// Ejecutar consulta para obtener el curso
$stmtCurso = $conn->prepare($sqlCurso);
$stmtCurso->execute([$cursoId]);
$cursoResult = $stmtCurso->fetch(PDO::FETCH_ASSOC);

if (!$cursoResult) {
    // Si no se encuentra el curso
    http_response_code(404);
    echo json_encode(['message' => 'Curso no encontrado']);
    exit();
}

// Ejecutar consulta para obtener las preguntas asociadas al curso
$stmtPreguntas = $conn->prepare($sqlPreguntas);
$stmtPreguntas->execute([$cursoId]);
$preguntasResult = $stmtPreguntas->fetchAll(PDO::FETCH_ASSOC);

// Agregar las preguntas al resultado del curso
$cursoResult['preguntas'] = $preguntasResult;

// Devolver el curso con las preguntas en formato JSON
echo json_encode($cursoResult);
?>
