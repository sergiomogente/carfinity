<?php
session_start();
require_once 'db_conexion.php';

if (!isset($_SESSION['id_cliente'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit;
}

$id_coche = $_POST['id_coche'] ?? null;

if (!$id_coche) {
    echo json_encode(['status' => 'error', 'message' => 'ID de coche no proporcionado']);
    exit;
}

$id_cliente = $_SESSION['id_cliente'];

// Verificar si ya está en favoritos
$query = "SELECT * FROM favoritos WHERE id_coche = ? AND id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $id_coche, $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Eliminar de favoritos
    $query = "DELETE FROM favoritos WHERE id_coche = ? AND id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $id_coche, $id_cliente);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'action' => 'eliminado']);
} else {
    // Agregar a favoritos
    $query = "INSERT INTO favoritos (id_coche, id_usuario) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $id_coche, $id_cliente);
    $stmt->execute();
    echo json_encode(['status' => 'success', 'action' => 'agregado']);
}
exit;
?>

