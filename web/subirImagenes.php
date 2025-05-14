<?php
$conn = new mysqli("localhost", "root", "", "carfinity");
if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_coche']) && isset($_FILES['imagenes'])) {
    $id_coche = intval($_POST['id_coche']);
    $imagenes = $_FILES['imagenes'];

    // Crear carpeta para el coche si no existe
    $carpeta = "assets/images/coche_carrusel/$id_coche";
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    // Subir imágenes
    for ($i = 0; $i < count($imagenes['name']); $i++) {
        $nombreArchivo = basename($imagenes['name'][$i]);
        $rutaDestino = "$carpeta/$nombreArchivo";

        if (move_uploaded_file($imagenes['tmp_name'][$i], $rutaDestino)) {
            // Guardar ruta en la base de datos
            $rutaRelativa = "$carpeta/$nombreArchivo";
            $stmt = $conn->prepare("INSERT INTO imagenes_coche (id_coche, ruta_imagen) VALUES (?, ?)");
            $stmt->bind_param("is", $id_coche, $rutaRelativa);
            $stmt->execute();
        }
    }

    header("Location: admin2.php");
    exit();
}
?>