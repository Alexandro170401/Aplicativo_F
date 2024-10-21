<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require 'db.php'; // Archivo de conexión a la base de datos

// Leer los datos JSON enviados desde el frontend
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    error_log('Error: no se pudieron recibir los datos JSON');
    echo json_encode(['message' => 'Error en el formato de los datos']);
    exit();
}

$cursoId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$cursoId) {
    error_log('Error: id del curso no proporcionado');
    echo json_encode(['message' => 'El ID del curso es requerido']);
    exit();
}

// Datos del curso que se van a actualizar
$nombre = isset($input['nombre']) ? $input['nombre'] : null;
$descripcion = isset($input['descripcion']) ? $input['descripcion'] : null;
$instructor = isset($input['instructor']) ? $input['instructor'] : null;
$especialidad = isset($input['especialidad']) ? $input['especialidad'] : null;
$detalleCurso = isset($input['detalleCurso']) ? $input['detalleCurso'] : null;

// Verificar si alguno de los campos obligatorios es nulo
if (!$nombre || !$descripcion || !$instructor || !$especialidad || !$detalleCurso) {
    error_log('Error: campos obligatorios faltantes');
    echo json_encode(['message' => 'Todos los campos son requeridos']);
    exit();
}

// Consulta SQL para actualizar el curso
$sqlCurso = "
    UPDATE cursos 
    SET nombre = ?, descripcion = ?, instructor = ?, especialidad = ?, detalle_curso = ?
    WHERE id = ?
";

$stmt = $conn->prepare($sqlCurso);
if (!$stmt->execute([$nombre, $descripcion, $instructor, $especialidad, $detalleCurso, $cursoId])) {
    echo json_encode(['message' => 'Error al actualizar el curso']);
    http_response_code(500);
    exit();
}

// Eliminar las preguntas antiguas asociadas al curso
$sqlDeletePreguntas = "DELETE FROM preguntas WHERE curso_id = ?";
$stmtDelete = $conn->prepare($sqlDeletePreguntas);
$stmtDelete->execute([$cursoId]);

// Insertar nuevas preguntas
if (!empty($input['preguntas'])) {
    $sqlInsertPreguntas = "
        INSERT INTO preguntas (curso_id, pregunta, especialidad, opcion1, opcion2, opcion3, opcion4, respuesta_correcta)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmtInsert = $conn->prepare($sqlInsertPreguntas);
    
    foreach ($input['preguntas'] as $pregunta) {
        $stmtInsert->execute([
            $cursoId,
            $pregunta['pregunta'],
            $pregunta['especialidad'],
            $pregunta['opcion1'],
            $pregunta['opcion2'],
            $pregunta['opcion3'],
            $pregunta['opcion4'],
            $pregunta['respuestaCorrecta']
        ]);
    }
}

echo json_encode(['message' => 'Curso actualizado con éxito']);
?>
