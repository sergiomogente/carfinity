<!-- filepath: c:\wamp64\www\carfinity\carfinity\web\procesar_contacto.php -->
<?php
require_once 'db_conexion.php'; // Conexión a la base de datos
session_start(); // Asegúrate de iniciar la sesión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
    $nombre = htmlspecialchars($_POST['nombre']);
    $email = htmlspecialchars($_POST['email']);
    $telefono = htmlspecialchars($_POST['telefono']);
    $contenido = htmlspecialchars($_POST['mensaje']);

    // Verificar si el usuario está autenticado
    if (isset($_SESSION['id_cliente']) && isset($_SESSION['nombre_cliente'])) {
        $id_chat = $_SESSION['id_cliente']; // ID del usuario autenticado
        $remitente = htmlspecialchars($_SESSION['nombre_cliente']); // Nombre del usuario autenticado
    } else {
        $id_chat = 0; // ID predeterminado para usuarios no autenticados
        $remitente = $nombre; // Nombre proporcionado en el formulario
    }

    // Depuración: Verificar valores
    error_log("ID Chat: $id_chat, Remitente: $remitente, Teléfono: $telefono, Gmail: $email, Contenido: $contenido");

    // Insertar en la base de datos
    $sql = "INSERT INTO mensaje (id_chat, remitente, telefono, gmail, contenido) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isiss", $id_chat, $remitente, $telefono, $email, $contenido);

    if ($stmt->execute()) {
        header("Location: contacto.php?success=1");
        exit();
    } else {
        error_log("Error en la consulta: " . $stmt->error);
        header("Location: contacto.php?error=1");
        exit();
    }
}
?>