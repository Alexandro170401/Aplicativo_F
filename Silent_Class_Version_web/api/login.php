<?php

header("Access-Control-Allow-Origin: *"); // Permite cualquier origen
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Encabezados permitidos
header("Access-Control-Allow-Credentials: true");

require 'db.php'; // Asegúrate de que este archivo contiene la configuración para PDO

// Leer el cuerpo de la solicitud JSON
$input = json_decode(file_get_contents('php://input'), true);

$correo = $input['correo'];
$contrasena = $input['contrasena'];

try {
    // Consulta SQL para buscar al usuario por correo
    $sql = "SELECT * FROM usuarios WHERE correo = :correo";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    
    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['message' => 'Correo o contraseña incorrectos']);
        exit();
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si la contraseña es correcta (usamos password_verify si las contraseñas están encriptadas)
    if (!password_verify($contrasena, $user['contrasena'])) {
        echo json_encode(['message' => 'Correo o contraseña incorrectos']);
        exit();
    }

    // Generar un token (opcional, puedes cambiarlo si lo necesitas)
    $token = bin2hex(random_bytes(16));

    // Preparar la respuesta
    $response = [
        'message' => 'Inicio de sesión exitoso',
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'tipoUsuario' => $user['tipousuario'], // Incluye el tipo de usuario
            'nombre' => $user['nombre'],
            'correo' => $user['correo']
        ]
    ];

    // Si el usuario es "Padre", agregar el id del estudiante relacionado
    if ($user['tipousuario'] === 'Padre') {
        $relationSql = "SELECT id_estudiante FROM padre_estudiante WHERE id_padre = :id_padre";
        $stmtRelation = $conn->prepare($relationSql);
        $stmtRelation->bindParam(':id_padre', $user['id']);
        $stmtRelation->execute();
        
        if ($stmtRelation->rowCount() > 0) {
            $relation = $stmtRelation->fetch(PDO::FETCH_ASSOC);
            $response['user']['estudianteRelacionado'] = $relation['id_estudiante']; // Agregar el ID del estudiante relacionado
        }
    }

    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['message' => 'Error en el servidor: ' . $e->getMessage()]);
}
