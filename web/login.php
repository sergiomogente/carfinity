<?php
session_start();
require_once 'db_conexion.php'; // Importamos la conexión a la base de datos

$password_pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

// Manejar el inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        echo "<script>alert('Error: Todos los campos son obligatorios.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Error: Formato de email incorrecto.');</script>";
    } else {
        $sql = "SELECT id_cliente, password FROM cliente WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id_cliente, $password_hashed);
            $stmt->fetch();

            if (password_verify($password, $password_hashed)) {
                $_SESSION['id_cliente'] = $id_cliente;
                echo "<script>alert('¡Inicio de sesión exitoso!'); window.location.href = 'pagina_principal.php';</script>";
                exit;
            } else {
                echo "<script>alert('Error: Contraseña incorrecta.');</script>";
            }
        } else {
            echo "<script>alert('Error: El email no está registrado.');</script>";
        }
        $stmt->close();
    }
}

// Manejar el registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'register') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($nombre) || empty($email) || empty($password)) {
        echo "<script>alert('Error: Todos los campos son obligatorios.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Error: Formato de email incorrecto.');</script>";
    } elseif (!preg_match($password_pattern, $password)) {
        echo "<script>alert('Error: La contraseña debe tener al menos 8 caracteres, incluir una letra mayúscula, una letra minúscula, un número y un carácter especial.');</script>";
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
                echo "<script>alert('¡Registro exitoso! Ahora puedes iniciar sesión.'); window.location.href = 'login.php';</script>";
            } else {
                echo "<script>alert('Error: No se pudo registrar. Inténtalo nuevamente.');</script>";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login y Register - Carfinity</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">

</head>
<body>

<main>
    <div class="contenedor__todo">
        <div class="caja__trasera">
            <div class="caja__trasera-login">
                <h3>¿Ya tienes una cuenta?</h3>
                <p>Inicia sesión para entrar en la página</p>
                <button id="btn__iniciar-sesion">Iniciar Sesión</button>
            </div>
            <div class="caja__trasera-register">
                <h3>¿Aún no tienes una cuenta?</h3>
                <p>Regístrate para que puedas iniciar sesión</p>
                <button id="btn__registrarse">Regístrarse</button>
            </div>
        </div>

        <!-- Formulario de Login y Registro -->
        <div class="contenedor__login-register">
            <!-- Login Form -->
            <form action="login.php" method="POST" class="formulario__login">
                <h2>Iniciar Sesión</h2>
                <input type="email" name="email" placeholder="Correo Electrónico" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <input type="hidden" name="action" value="login">
                <button type="submit">Entrar</button>
            </form>

            <!-- Register Form -->
            <form action="login.php" method="POST" class="formulario__register">
                <h2>Regístrarse</h2>
                <input type="text" name="nombre" placeholder="Nombre completo" required>
                <input type="email" name="email" placeholder="Correo Electrónico" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <input type="hidden" name="action" value="register">
                <button type="submit">Regístrarse</button>
            </form>
        </div>
    </div>
    
</main>
<footer class="pie-pagina">
        <div class="grupo-1">
            <div class="box">
                <figure>
                    <a href="#">
                        <img src="logo_blanco.png" alt="">
                    </a>
                </figure>
            </div>
            <div class="box">
                <h2>SOBRE NOSOTROS</h2>
                <p>Somos una empresa dedicada a la compra, venta y financiamiento de vehículos, comprometida con ofrecer calidad, tecnología y confianza a nuestros clientes. </p>
                <p>Nuestro objetivo es simplificar el proceso de adquisición de un automóvil, brindando soluciones rápidas, seguras y accesibles.</p>
            </div>
            <div class="box">
                <h2>SIGUENOS</h2>
                <div class="red-social">
                    <i class="bx bxl-facebook"></i>
                    <i class="bx bxl-instagram"></i>
                    <i class="bx bxl-twitter"></i>
                    <i class="bx bxl-youtube"></i>
                </div>
            </div>
        </div>
        <div class="grupo-2">
            <small>&copy; 2025 <b>Carfinity</b> - Todos los Derechos Reservados.</small>
        </div>
    </footer>
<script src="assets/js/script.js"></script>
</body>
</html>
