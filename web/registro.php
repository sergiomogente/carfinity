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
    if (!isset($_POST['nombre'], $_POST['email'], $_POST['password'])) {
        $mensaje = "Error: Datos incompletos.";
    } else {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if (empty($nombre) || empty($email) || empty($password)) {
            $mensaje = "Error: Todos los campos son obligatorios.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "Error: Formato de email incorrecto.";
        } elseif (strlen($password) < 8) {
            $mensaje = "Error: La contraseña debe tener al menos 8 caracteres.";
        } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $mensaje = "Error: La contraseña debe contener al menos una letra y un número.";
        } elseif (in_array($password, ["12345678", "password", "qwerty", "abcdefg", "87654321"])) {
            $mensaje = "Error: La contraseña no es válida.";
        } else {
            $sql_check = "SELECT id_cliente FROM cliente WHERE email = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $mensaje = "Error: Este email ya está registrado.";
            } else {
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO cliente (nombre, email, password) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $nombre, $email, $password_hashed);

                if ($stmt->execute()) {
                    $mensaje = "Registro exitoso.";
                    $clase_mensaje = "exito";
                } else {
                    $mensaje = "Error al registrar: " . $conn->error;
                }
                $stmt->close();
            }
            $stmt_check->close();
        }
    }
}
$conn->close();
?>

<script type="text/javascript">
    <?php if ($mensaje): ?>
        alert("<?php echo $mensaje; ?>");
    <?php endif; ?>
</script>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Carfinity</title>
    <link rel="stylesheet" href="registro.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>
    
    <div class="container">
        <header>
            <img src="logo_negro.png" alt="Carfinity Logo" class="logo">
        </header>

        <div class="form-container">
            <h2>REGISTRO</h2>
            <form action="registro.php" method="POST">
                <label for="nombre">Nombre de usuario</label>
                <input type="text" name="nombre" placeholder="Nombre" required>

                <label for="email">Email</label>
                <input type="email" name="email" placeholder="Email" required>

                <label for="password">Contraseña</label>
                <input type="password" name="password" placeholder="Contraseña " required>

                <button type="submit">Registrar</button>
                <button type="button" class="btn-secondary" onclick="window.location.href='login.php'">Iniciar Sesión</button>

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
