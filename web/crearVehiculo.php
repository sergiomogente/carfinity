<?php
$conn = new mysqli("localhost", "root", "", "carfinity");
if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = $conn->real_escape_string($_POST['marca']);
    $modelo = $conn->real_escape_string($_POST['modelo']);
    $precio = floatval($_POST['precio']);
    $km = floatval($_POST['km']);
    $combustible = $conn->real_escape_string($_POST['combustible']);
    $transmision = $conn->real_escape_string($_POST['transmision']);
    $caracteristicas = $conn->real_escape_string($_POST['caracterisitcas']);
    $color = $conn->real_escape_string($_POST['color']);
    $año = intval($_POST['año']);
    $reservado = intval($_POST['reservado']);

    // Procesar la imagen
    $foto_portada = null;
    if (isset($_FILES['foto_portada']) && $_FILES['foto_portada']['error'] === UPLOAD_ERR_OK) {
        $foto_portada = file_get_contents($_FILES['foto_portada']['tmp_name']);
    }

    // Insertar en la base de datos
    $stmt = $conn->prepare("INSERT INTO coche (marca, modelo, precio, km, combustible, transmision, caracterisitcas, color, año, reservado, foto_portada) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddssssibs", $marca, $modelo, $precio, $km, $combustible, $transmision, $caracteristicas, $color, $año, $reservado, $foto_portada);
    $stmt->send_long_data(10, $foto_portada); // Enviar datos binarios para el campo foto_portada

    if ($stmt->execute()) {
        header("Location: admin2.php");
        exit();
    } else {
        echo "Error al agregar el coche: " . $stmt->error;
    }
}

$conn->close();
?>