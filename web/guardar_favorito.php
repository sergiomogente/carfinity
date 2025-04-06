<?php
session_start();
require_once 'db_conexion.php';

if (isset($_POST['id_coche']) && isset($_SESSION['id_cliente'])) {
    $id_coche = $_POST['id_coche'];
    $id_cliente = $_SESSION['id_cliente'];
    $origen = $_POST['origen'] ?? ''; // puede ser 'destacados' o 'generico'

    // Verificar si ya está en favoritos
    $sql = "SELECT * FROM favoritos WHERE id_coche = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_coche, $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if ($origen === 'destacados') {
            // Eliminar solo si viene desde destacados
            $sql_delete = "DELETE FROM favoritos WHERE id_coche = ? AND id_usuario = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("ii", $id_coche, $id_cliente);
            $stmt_delete->execute();
            echo "eliminado";
        } else {
            echo "ya_favorito"; // No hacer nada si ya está
        }
    } else {
        // Agregar si no está
        $sql_insert = "INSERT INTO favoritos (id_coche, id_usuario) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $id_coche, $id_cliente);
        $stmt_insert->execute();
        echo "agregado";
    }
} else {
    echo "error";
}
?>

