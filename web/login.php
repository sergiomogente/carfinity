<?php
session_start();
require_once 'db_conexion.php';

// Inicializar el contador de intentos fallidos si no existe
if (!isset($_SESSION['intentos_fallidos'])) {
    $_SESSION['intentos_fallidos'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        echo "<script>alert('Error: Todos los campos son obligatorios.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Error: Formato de email incorrecto.');</script>";
    } else {
        $sql = "SELECT id_cliente, nombre, apellidos, password, es_admin FROM cliente WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id_cliente, $nombre, $apellidos, $password_hashed, $es_admin);
            $stmt->fetch();

            if (password_verify($password, $password_hashed)) {
                // Restablecer el contador de intentos fallidos
                $_SESSION['intentos_fallidos'] = 0;

                $_SESSION['id_cliente'] = $id_cliente;
                $_SESSION['nombre_cliente'] = $nombre . ' ' . $apellidos;
                $_SESSION['es_admin'] = $es_admin;

                if ($es_admin == 1) {
                    echo "<script>alert('¡Inicio de sesión exitoso como administrador!'); window.location.href = 'admin2.php';</script>";
                } else {
                    echo "<script>alert('¡Inicio de sesión exitoso!'); window.location.href = 'pagina_principal.php';</script>";
                }
                exit;
            } else {
                $_SESSION['intentos_fallidos']++;
                echo "<script>alert('Error: Contraseña incorrecta.');</script>";
            }
        } else {
            $_SESSION['intentos_fallidos']++;
            echo "<script>alert('Error: El email no está registrado.');</script>";
        }
        $stmt->close();
    }
}

// Mostrar alerta si se alcanzan 3 intentos fallidos
if ($_SESSION['intentos_fallidos'] >= 3) {
    echo "<script>alert('Has alcanzado el máximo de intentos fallidos. Por favor, restablece tu contraseña.');</script>";
}
?>

<!-- Formulario de Login -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Carfinity</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">

</head>
<body>
    <div class="container">
        <form class="form" method="POST" action="login.php">
            <h2>Iniciar Sesión</h2>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Contraseña" required>
                <button type="button" class="toggle-password" onclick="togglePassword()"><i class='bx bx-hide'></i></button>
            </div>
            <button type="submit">Entrar</button>
            <?php
            // Mostrar enlace de restablecer contraseña si se alcanzan 3 intentos fallidos
            if ($_SESSION['intentos_fallidos'] >= 3) {
                echo "<p class='reset-password'><a href='restablecer_contraseña.php'>¿Olvidaste tu contraseña? Restablécela aquí</a></p>";
            }
            ?>
            <p>¿No tienes cuenta? <a href="registrar.php">Regístrate</a></p>
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
