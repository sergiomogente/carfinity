<?php
require_once 'db_conexion.php';

$password_pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($nombre) || empty($email) || empty($password)) {
        echo "<script>alert('Error: Todos los campos son obligatorios.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Error: Formato de email incorrecto.');</script>";
    } elseif (!preg_match($password_pattern, $password)) {
        echo "<script>alert('Error: La contraseña debe tener al menos 8 caracteres, incluir una letra mayúscula, una minúscula, un número y un carácter especial.');</script>";
    } else {
        $sql = "SELECT id_cliente FROM cliente WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('Error: El email ya está registrado.');</script>";
        } else {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO cliente (nombre, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $nombre, $email, $password_hashed);

            if ($stmt->execute()) {
                echo "<script>alert('¡Registro exitoso!'); window.location.href = 'login.php';</script>";
            } else {
                echo "<script>alert('Error: No se pudo registrar.');</script>";
            }
        }
        $stmt->close();
    }
}
?>
<!-- Formulario de Registro -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Carfinity</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <form class="form" method="POST" action="registrar.php">
            <h2>Regístrate</h2>
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Contraseña" required>
                <button type="button" class="toggle-password" onclick="togglePassword()"><i class='bx bx-hide'></i></button>
            </div>
            <button type="submit">Crear cuenta</button>
            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
        </form>
    </div>

    <script>
    function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.querySelector('.toggle-password');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.innerHTML = "<i class='bx bx-show'></i>";
    } else {
        passwordInput.type = 'password';
        toggleButton.innerHTML = "<i class='bx bx-hide'></i>";
    }
}
</script>
</body>
</html>