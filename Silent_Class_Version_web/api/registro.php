<?php

header("Access-Control-Allow-Origin: *"); // Permitir cualquier origen
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Encabezados permitidos
header("Access-Control-Allow-Credentials: true");

require 'db.php'; // Archivo de conexión a la base de datos

// Leer el cuerpo de la solicitud JSON
$input = json_decode(file_get_contents('php://input'), true);

// Recibir los datos de la solicitud
$tipousuario = $input['tipousuario'];
$nombre = $input['nombre'];
$apellido_paterno = $input['apellido_paterno'];
$apellido_materno = isset($input['apellido_materno']) ? $input['apellido_materno'] : null; // Opcional
$dni = $input['dni'];
$correo = $input['correo'];
$contrasena = password_hash($input['contrasena'], PASSWORD_BCRYPT); // Encriptar la contraseña con bcrypt
$id_estudiante = isset($input['id_estudiante']) ? $input['id_estudiante'] : null; // Opcional, solo si es un padre

try {
    // Consulta SQL para insertar un nuevo usuario
    $sql = "INSERT INTO usuarios (tipousuario, nombre, apellido_paterno, apellido_materno, dni, correo, contrasena) 
            VALUES (:tipousuario, :nombre, :apellido_paterno, :apellido_materno, :dni, :correo, :contrasena)";

    $stmt = $conn->prepare($sql);

    // Vincular los valores a la consulta preparada
    $stmt->bindParam(':tipousuario', $tipousuario);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido_paterno', $apellido_paterno);
    $stmt->bindParam(':apellido_materno', $apellido_materno);
    $stmt->bindParam(':dni', $dni);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':contrasena', $contrasena);

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener el ID del usuario insertado
    $userId = $conn->lastInsertId();

    // Si el usuario es "Padre", crear la relación con el estudiante
    if ($tipousuario === 'Padre' && $id_estudiante) {
        $relationSql = "INSERT INTO padre_estudiante (id_padre, id_estudiante) VALUES (:id_padre, :id_estudiante)";
        $stmtRelation = $conn->prepare($relationSql);
        $stmtRelation->bindParam(':id_padre', $userId);
        $stmtRelation->bindParam(':id_estudiante', $id_estudiante);
        
        // Ejecutar la consulta para crear la relación
        $stmtRelation->execute();
    }

    // Devolver una respuesta exitosa con el ID del usuario recién insertado
    $response = [
        'message' => 'Usuario registrado con éxito',
        'userId' => $userId
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    // Si ocurre un error, devolver un mensaje de error
    echo json_encode(['message' => 'Error al crear el usuario: ' . $e->getMessage()]);
}

?>

