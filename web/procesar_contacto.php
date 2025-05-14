<!-- filepath: c:\wamp64\www\carfinity\carfinity\web\procesar_contacto.php -->
<?php
session_start();

if (!isset($_SESSION['id_cliente'])) {
    // Si no ha iniciado sesión, redirigir al formulario con un mensaje de error
    echo "<script>alert('Debes iniciar sesión para enviar el formulario.'); window.location.href = 'contacto.php';</script>";
    exit();
}

// Aquí procesas el formulario si el usuario ha iniciado sesión
require_once 'db_conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir datos del formulario
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $mensaje = trim($_POST['mensaje']);

    // Validar los campos
    if (empty($nombre) || empty($email) || empty($telefono) || empty($mensaje)) {
        echo "<script>alert('Todos los campos son obligatorios.'); window.location.href = 'contacto.php';</script>";
        exit();
    }

    // Verificar si el usuario está autenticado
    if (isset($_SESSION['id_cliente']) && isset($_SESSION['nombre_cliente'])) {
        $id_chat = $_SESSION['id_cliente']; // ID del usuario autenticado
        $remitente = htmlspecialchars($_SESSION['nombre_cliente']); // Nombre del usuario autenticado
    } else {
        $id_chat = 0; // ID predeterminado para usuarios no autenticados
        $remitente = $nombre; // Nombre proporcionado en el formulario
    }

    // Depuración: Verificar valores
    error_log("ID Chat: $id_chat, Remitente: $remitente, Teléfono: $telefono, Gmail: $email, Contenido: $mensaje");

    // Insertar en la base de datos
    $sql = "INSERT INTO mensaje (id_chat, remitente, telefono, gmail, contenido) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isiss", $id_chat, $remitente, $telefono, $email, $mensaje);

    if ($stmt->execute()) {
        echo "<script>alert('Tu consulta ha sido enviada con éxito.'); window.location.href = 'contacto.php';</script>";
    } else {
        echo "<script>alert('Hubo un error al enviar tu consulta. Inténtalo de nuevo.'); window.location.href = 'contacto.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>