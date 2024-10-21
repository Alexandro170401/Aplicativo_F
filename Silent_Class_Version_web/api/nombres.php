<?php
// Habilitar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include 'db.php';

$sql = "SELECT nombre FROM usuarios";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $nombres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($nombres);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
