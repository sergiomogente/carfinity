<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_imagen'])) {
    $id_imagen = intval($_POST['id_imagen']);

    $conn = new mysqli("localhost", "root", "", "carfinity");
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Error al conectar con la base de datos.']);
        exit();
    }

    // Obtener la ruta de la imagen para eliminar el archivo físico
    $result = $conn->query("SELECT ruta_imagen FROM imagenes_coche WHERE id_imagen = $id_imagen");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $ruta_imagen = $row['ruta_imagen'];

        // Eliminar el registro de la base de datos
        if ($conn->query("DELETE FROM imagenes_coche WHERE id_imagen = $id_imagen")) {
            // Eliminar el archivo físico
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
            echo json_encode(['success' => true, 'message' => 'Imagen eliminada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la imagen de la base de datos.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Imagen no encontrada.']);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}
?>