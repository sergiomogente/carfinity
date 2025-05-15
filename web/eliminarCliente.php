<?php
session_start();
require_once 'db_conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cliente'])) {
    $id_cliente = intval($_POST['id_cliente']);

    // Verificar si el cliente tiene reservas asociadas
    $reservas = $conn->query("SELECT COUNT(*) AS total FROM reserva WHERE id_cliente = $id_cliente")->fetch_assoc()['total'];
    if ($reservas > 0) {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar el cliente porque tiene reservas asociadas.']);
        exit();
    }

    // Eliminar el cliente
    $stmt = $conn->prepare("DELETE FROM cliente WHERE id_cliente = ?");
    $stmt->bind_param("i", $id_cliente);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cliente eliminado con éxito.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el cliente.']);
    }

    $stmt->close();
    $conn->close();
}
?>