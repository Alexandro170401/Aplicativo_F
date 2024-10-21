<?php
// Cabeceras para CORS y manejo de peticiones
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Si la solicitud es un OPTIONS, retornar sin procesar nada más
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir el archivo de conexión a la base de datos
require 'db.php';

// Leer el cuerpo de la solicitud
$input = json_decode(file_get_contents('php://input'), true);

// Asegurarse de que los datos requeridos están presentes
if (!isset($input['cursoId'], $input['usuarioId'], $input['respuestas'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Faltan datos requeridos']);
    exit();
}

// Variables obtenidas del cuerpo de la solicitud
$cursoId = $input['cursoId'];
$usuarioId = $input['usuarioId'];
$respuestas = $input['respuestas'];

// Obtener la fecha actual ajustada a GMT-5
$fechaAjustada = new DateTime('now', new DateTimeZone('America/Lima'));
$fechaAjustada = $fechaAjustada->format('Y-m-d H:i:s');

// Consulta SQL para obtener las preguntas del curso
$sql = 'SELECT * FROM preguntas WHERE curso_id = ?';

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$cursoId]);
    $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inicializar las variables para contar las respuestas correctas por especialidad
    $correctasMatematicas = 0;
    $correctasLenguaje = 0;
    $correctasHistoria = 0;
    $totalMatematicas = 0;
    $totalLenguaje = 0;
    $totalHistoria = 0;

    foreach ($preguntas as $pregunta) {
        // Verificar si hay una respuesta para la pregunta actual
        if (isset($respuestas[$pregunta['id']])) {
            $respuestaUsuario = $respuestas[$pregunta['id']];

            // Si la respuesta del usuario es correcta
            if ($respuestaUsuario == $pregunta['respuesta_correcta']) {
                switch ($pregunta['especialidad']) {
                    case 'Matematicas':
                        $correctasMatematicas++;
                        break;
                    case 'Lenguaje':
                        $correctasLenguaje++;
                        break;
                    case 'Historia':
                        $correctasHistoria++;
                        break;
                }
            }
        }

        // Contabilizar la pregunta en el total de cada especialidad
        switch ($pregunta['especialidad']) {
            case 'Matematicas':
                $totalMatematicas++;
                break;
            case 'Lenguaje':
                $totalLenguaje++;
                break;
            case 'Historia':
                $totalHistoria++;
                break;
        }
    }

    // Calcular las notas de 0 a 20
    $notaMatematicas = $totalMatematicas > 0 ? ($correctasMatematicas / $totalMatematicas) * 20 : 0;
    $notaLenguaje = $totalLenguaje > 0 ? ($correctasLenguaje / $totalLenguaje) * 20 : 0;
    $notaHistoria = $totalHistoria > 0 ? ($correctasHistoria / $totalHistoria) * 20 : 0;

    // Insertar los resultados de la evaluación en la base de datos
    $sqlInsert = 'INSERT INTO evaluaciones (curso_id, usuario_id, fecha, nota_matematicas, nota_lenguaje, nota_historia)
                  VALUES (?, ?, ?, ?, ?, ?)';
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->execute([$cursoId, $usuarioId, $fechaAjustada, $notaMatematicas, $notaLenguaje, $notaHistoria]);

    echo json_encode(['evaluacionId' => $conn->lastInsertId()]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error al procesar la evaluación', 'error' => $e->getMessage()]);
}

// Cerrar la conexión
$conn = null;
?>
