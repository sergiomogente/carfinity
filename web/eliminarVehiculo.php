<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_coche'])) {
    $id_coche = intval($_POST['id_coche']);

    $conn = new mysqli("localhost", "root", "", "carfinity");
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Error al conectar con la base de datos.']);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM coche WHERE id_coche = ?");
    $stmt->bind_param("i", $id_coche);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Coche eliminado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el coche.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}
?>