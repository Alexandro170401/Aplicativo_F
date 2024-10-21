<?php

// Habilitar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';  // Incluir la conexión a la base de datos

// Consulta SQL para obtener la lista de estudiantes
$sql = "SELECT id, nombre, apellido_paterno, apellido_materno FROM usuarios WHERE tipousuario = 'Estudiante'";

try {
    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Obtener los resultados como un array asociativo
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Crear un array de estudiantes con el nombre completo concatenado
    $estudiantesConNombreCompleto = array_map(function($estudiante) {
        return [
            'id' => $estudiante['id'],
            'nombreCompleto' => trim($estudiante['nombre'] . ' ' . $estudiante['apellido_paterno'] . ' ' . $estudiante['apellido_materno'])
        ];
    }, $estudiantes);
    
    // Enviar la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($estudiantesConNombreCompleto);

} catch (PDOException $e) {
    // Enviar un mensaje de error en caso de fallo
    echo json_encode(["Error" => $e->getMessage()]);
}

?>

