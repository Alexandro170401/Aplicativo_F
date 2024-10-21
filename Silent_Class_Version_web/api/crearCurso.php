<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Las solicitudes preflight de CORS envían una solicitud OPTIONS al servidor para verificar permisos
    http_response_code(200);
    exit();
}

// Conexión a la base de datos
require 'db.php';

// Leer el cuerpo de la solicitud JSON
$input = json_decode(file_get_contents('php://input'), true);

$nombre = $input['nombre'];
$descripcion = $input['descripcion'];
$instructor = $input['instructor'];
$especialidad = $input['especialidad'];
$detalleCurso = $input['detalleCurso'];
$preguntas = isset($input['preguntas']) ? $input['preguntas'] : [];

// Consulta para insertar el curso
$sql = "INSERT INTO cursos (nombre, descripcion, instructor, especialidad, detalle_curso) 
        VALUES (:nombre, :descripcion, :instructor, :especialidad, :detalle_curso)";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':descripcion', $descripcion);
$stmt->bindParam(':instructor', $instructor);
$stmt->bindParam(':especialidad', $especialidad);
$stmt->bindParam(':detalle_curso', $detalleCurso);

if ($stmt->execute()) {
    $cursoId = $conn->lastInsertId(); // Obtener el ID del curso recién insertado

    // Si hay preguntas, insertarlas en la tabla correspondiente
    if (!empty($preguntas)) {
        $preguntaSql = "INSERT INTO preguntas (curso_id, pregunta, especialidad, opcion1, opcion2, opcion3, opcion4, respuesta_correcta)
                        VALUES (:curso_id, :pregunta, :especialidad, :opcion1, :opcion2, :opcion3, :opcion4, :respuesta_correcta)";
        
        $preguntaStmt = $conn->prepare($preguntaSql);

        // Recorrer las preguntas y ejecutarlas
        foreach ($preguntas as $pregunta) {
            $preguntaStmt->bindParam(':curso_id', $cursoId);
            $preguntaStmt->bindParam(':pregunta', $pregunta['pregunta']);
            $preguntaStmt->bindParam(':especialidad', $pregunta['especialidad']);
            $preguntaStmt->bindParam(':opcion1', $pregunta['opciones'][0]);
            $preguntaStmt->bindParam(':opcion2', $pregunta['opciones'][1]);
            $preguntaStmt->bindParam(':opcion3', $pregunta['opciones'][2]);
            $preguntaStmt->bindParam(':opcion4', $pregunta['opciones'][3]);
            $respuestaCorrecta = $pregunta['respuestaCorrecta'] + 1; // Ajustar la respuesta correcta
            $preguntaStmt->bindParam(':respuesta_correcta', $respuestaCorrecta);
            
            // Ejecutar la inserción de cada pregunta
            if (!$preguntaStmt->execute()) {
                echo json_encode(['message' => 'Error al crear las preguntas del curso']);
                exit();
            }
        }

        // Si todo fue exitoso
        echo json_encode(['message' => 'Curso y preguntas creadas con éxito']);
    } else {
        echo json_encode(['message' => 'Curso creado con éxito, sin preguntas']);
    }
} else {
    echo json_encode(['message' => 'Error al crear el curso']);
}
?>
