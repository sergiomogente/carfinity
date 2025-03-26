<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "carfinity";

// Conectar con MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$mensaje = "";
$clase_mensaje = "error"; // Clase CSS para estilos de error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['email'], $_POST['password'])) {
        $mensaje = "Error: Datos incompletos.";
    } else {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if (empty($email) || empty($password)) {
            $mensaje = "Error: Todos los campos son obligatorios.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "Error: Formato de email incorrecto.";
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
                    $mensaje = "Inicio de sesión exitoso.";
                    $clase_mensaje = "exito";
                    session_start();
                    $_SESSION['id_cliente'] = $id_cliente;
                    header("Location: pagina_principal.php"); 
                    exit();
                } else {
                    $mensaje = "Error: Contraseña incorrecta.";
                }
            } else {
                $mensaje = "Error: No existe una cuenta con este email.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Carfinity</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>

    <div class="container">
        <header>
            <img src="logo_negro.png" alt="Carfinity Logo" class="logo">
        </header>

        <div class="form-container">
            <h2>INICIAR SESIÓN</h2>
            <form action="login.php" method="POST">
                <label for="email">Email</label>
                <input type="email" name="email" placeholder="Email" required>

                <label for="password">Contraseña</label>
                <input type="password" name="password" placeholder="Contraseña" required>

                <button type="submit">Iniciar Sesión</button>
                <button type="button" class="btn-secondary" onclick="window.location.href='registro.php'">Registrarse</button>
            </form>
        </div>
        <footer>
    <div class="info">
        <p><i class='bx bx-map'></i> Carrer Jaume Viladoms, 19, 08204 Sabadell</p>
        <p><i class='bx bx-phone'></i> +34 601009976</p>
        <p><i class='bx bx-envelope'></i> sergiomogente32@gmail.com</p>
    </div>
    <div class="links">
        <h3>Marcas</h3>
        <p>
            <a href="#">BMW</a> | 
            <a href="#">Mercedes</a> | 
            <a href="#">Alpine</a> | 
            <a href="#">Audi</a> | 
            <a href="#">Nissan</a> | 
            <a href="#">Mini</a>
        </p>
    </div>
    <div class="menu">
        <h3>Menú</h3>
        <p>
            <a href="#">Inicio</a> | 
            <a href="#">Quién somos</a> | 
            <a href="#">Servicios</a> | 
            <a href="#">Vehículos</a> | 
            <a href="#">Contacto</a>
        </p>
    </div>

</footer>

    </div>

</body>
</html>
