<?php
require_once 'db_conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $nueva_password = trim($_POST['nueva_password']);
    $confirmar_password = trim($_POST['confirmar_password']);

    if (empty($email) || empty($nueva_password) || empty($confirmar_password)) {
        echo "<script>alert('Error: Todos los campos son obligatorios.');</script>";
    } elseif ($nueva_password !== $confirmar_password) {
        echo "<script>alert('Error: Las contraseñas no coinciden.');</script>";
    } else {
        $password_hashed = password_hash($nueva_password, PASSWORD_DEFAULT);

        $sql = "UPDATE cliente SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $password_hashed, $email);

        if ($stmt->execute()) {
            echo "<script>alert('¡Contraseña restablecida con éxito!'); window.location.href = 'login.php';</script>";
        } else {
            echo "<script>alert('Error: No se pudo restablecer la contraseña.');</script>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="container">
        <form class="form" method="POST" action="restablecer_contraseña.php">
            <h2>Restablecer Contraseña</h2>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="nueva_password" placeholder="Nueva Contraseña" required>
            <input type="password" name="confirmar_password" placeholder="Confirmar Contraseña" required>
            <button type="submit">Restablecer</button>
        </form>
    </div>
</body>
</html>