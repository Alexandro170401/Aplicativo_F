<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require 'db.php';

// Obtener el usuarioId de la solicitud GET
$usuarioId = $_GET['usuarioId'] ?? null;

if (!$usuarioId) {
    echo json_encode(['error' => 'Usuario no identificado']);
    exit();
}

// Verificar el tipo de usuario
$sql = "SELECT tipousuario FROM usuarios WHERE id = :usuarioId";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuarioId', $usuarioId);
$stmt->execute();
$tipoUsuario = $stmt->fetch(PDO::FETCH_ASSOC)['tipousuario'];

if ($tipoUsuario === 'Padre') {
    // Obtener el id_estudiante relacionado con el padre
    $relationSql = "SELECT id_estudiante FROM padre_estudiante WHERE id_padre = :usuarioId";
    $stmtRelation = $conn->prepare($relationSql);
    $stmtRelation->bindParam(':usuarioId', $usuarioId);
    $stmtRelation->execute();
    $relation = $stmtRelation->fetch(PDO::FETCH_ASSOC);

    if (!$relation) {
        echo json_encode(['message' => 'No se encontró el estudiante relacionado']);
        exit();
    }

    // Obtener el id del estudiante relacionado
    $estudianteId = $relation['id_estudiante'];
    error_log("Padre consultando por evaluaciones del estudiante relacionado: $estudianteId");
    // El padre ahora obtiene las evaluaciones completas del estudiante relacionado
    obtenerEvaluaciones($estudianteId, $conn);
} else {
    // Si es un estudiante, obtener las evaluaciones directamente
    obtenerEvaluaciones($usuarioId, $conn);
}

// Función auxiliar para obtener todas las evaluaciones de un usuario o estudiante relacionado
function obtenerEvaluaciones($usuarioId, $conn) {
    $sql = "
        SELECT e.id, c.nombre AS curso, c.especialidad, e.nota_matematicas, e.nota_lenguaje, e.nota_historia, e.fecha
        FROM evaluaciones e
        JOIN cursos c ON e.curso_id = c.id
        WHERE e.usuario_id = :usuarioId
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuarioId', $usuarioId);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        // Devolver las notas sin filtrado, tal como lo vería el estudiante
        echo json_encode($results);
    } else {
        echo json_encode(['message' => 'No se encontraron evaluaciones']);
    }
}
?>