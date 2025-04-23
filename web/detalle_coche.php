<?php
require_once 'db_conexion.php';
session_start();

// Verificar si el cliente está autenticado
if (!isset($_SESSION['id_cliente'])) {
    header("Location: login.php"); // Redirige al inicio de sesión si no está autenticado
    exit;
}

$id_cliente = $_SESSION['id_cliente'];

// Obtener el ID del coche desde la URL
$id_coche = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_coche <= 0) {
    echo "<p>ID de coche no válido.</p>";
    exit;
}

error_log("ID del coche recibido: " . $id_coche);

// Manejar la solicitud de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_coche = isset($_POST['id_coche']) ? intval($_POST['id_coche']) : 0;

    if ($id_coche <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
        exit;
    }

    // Verificar si el coche existe en la base de datos
    $sql_check_coche = "SELECT COUNT(*) AS count FROM coche WHERE id_coche = ?";
    $stmt_check_coche = $conn->prepare($sql_check_coche);
    $stmt_check_coche->bind_param("i", $id_coche);
    $stmt_check_coche->execute();
    $result_check_coche = $stmt_check_coche->get_result();
    $row_check_coche = $result_check_coche->fetch_assoc();

    if ($row_check_coche['count'] == 0) {
        echo json_encode(['status' => 'error', 'message' => 'El coche no existe en la base de datos.']);
        exit;
    }

    // Iniciar una transacción
    $conn->begin_transaction();

    try {
        // Insertar la reserva en la tabla `reserva`
        $sql_reserva = "INSERT INTO reserva (id_cliente, id_coche, fecha) VALUES (?, ?, NOW())";
        $stmt_reserva = $conn->prepare($sql_reserva);
        $stmt_reserva->bind_param("ii", $id_cliente, $id_coche);
        $stmt_reserva->execute();

        // Verificar si la reserva se insertó correctamente
        if ($stmt_reserva->affected_rows === 0) {
            throw new Exception('No se pudo guardar la reserva.');
        }

        // Eliminar el coche de la tabla `coche`
        $sql_delete_coche = "DELETE FROM coche WHERE id_coche = ?";
        $stmt_delete_coche = $conn->prepare($sql_delete_coche);
        $stmt_delete_coche->bind_param("i", $id_coche);
        $stmt_delete_coche->execute();

        // Verificar si el coche se eliminó correctamente
        if ($stmt_delete_coche->affected_rows === 0) {
            throw new Exception('No se pudo eliminar el coche.');
        }

        // Confirmar la transacción
        $conn->commit();

        echo json_encode(['status' => 'success', 'message' => 'Reserva realizada con éxito.']);
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Consultar los detalles del coche
$sql = "SELECT * FROM coche WHERE id_coche = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_coche);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $coche = $result->fetch_assoc();
} else {
    echo "<p>No se encontraron detalles para este coche.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Coche</title>
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/detalle_coche.css">
</head>
<body>
    <!-- Header -->
    <header class="animate">
        <div class="logo">
            <img src="logo_blanco.png" alt="Logo">
        </div>
        <nav>
            <ul>
                <li><a href="pagina_principal.php">Inicio</a></li>
                <li><a href="quien_somos.php">Quiénes somos</a></li>
                <li><a href="servicios.php">Servicios</a></li>
                <li><a href="contacto.php">Contacto</a></li>
            </ul>
        </nav>
        <div class="user-icon">
            <i class='bx bxs-user-circle'></i>
            <?php if (isset($_SESSION['id_cliente']) && isset($_SESSION['nombre_cliente'])): ?>
                <span><?= htmlspecialchars($_SESSION['nombre_cliente']) ?></span>
                <a href="logout.php"><i class='bx bx-log-out'></i></a>
            <?php else: ?>
                <a href="login.php">Iniciar sesión</a> /
                <a href="registrar.php">Registrar</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Contenido principal -->
    <section class="titulo animate">
        <h1 class="heading-explore">Detalles del Coche</h1>
    </section>

    <div class="detalle-coche-container animate">
        <div class="caracteristicas">
            <h1><?= htmlspecialchars($coche['modelo']) ?></h1>
            <p><strong>Marca:</strong> <?= htmlspecialchars($coche['marca']) ?></p>
            <p><strong>Año:</strong> <?= htmlspecialchars($coche['año']) ?></p>
            <p><strong>Precio:</strong> <?= number_format($coche['precio'], 3) ?> €</p>
            <p><strong>Color:</strong> <?= htmlspecialchars($coche['color']) ?></p>
            <p><strong>Combustible:</strong> <?= htmlspecialchars($coche['combustible']) ?></p>
            <p><strong>Kilometraje:</strong> <?= number_format($coche['km'], 3) ?> km</p>
            <p><strong>Transmisión:</strong> <?= htmlspecialchars($coche['transmision']) ?></p>
            <p><strong>Características:</strong> <?= htmlspecialchars($coche['caracterisitcas']) ?></p>
        </div>
        <div class="reservar-container">
            <button class="reservar-btn" onclick="reservarCoche(<?= $id_coche ?>)">Reservar ya</button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="animate">
        <div class="footer-container">
            <div class="footer-section">
                <h3>Carfinity</h3>
                <p>Tu mejor opción para encontrar el coche de tus sueños.</p>
            </div>
            <div class="footer-section">
                <h4>Enlaces rápidos</h4>
                <ul>
                    <li><a href="pagina_principal.php">Inicio</a></li>
                    <li><a href="quien_somos.php">Quiénes somos</a></li>
                    <li><a href="servicios.php">Servicios</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contáctanos</h4>
                <p><i class='bx bx-envelope'></i> contacto@carfinity.com</p>
                <p><i class='bx bx-phone'></i> +34 123 456 789</p>
                <p><i class='bx bx-map'></i> Calle Ejemplo, 123, Madrid</p>
            </div>
            <div class="footer-section">
                <h4>Síguenos</h4>
                <div class="social-icons">
                    <a href="#"><i class='bx bxl-facebook'></i></a>
                    <a href="#"><i class='bx bxl-twitter'></i></a>
                    <a href="#"><i class='bx bxl-instagram'></i></a>
                    <a href="#"><i class='bx bxl-linkedin'></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Carfinity. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        function reservarCoche(idCoche) {
            if (!confirm("¿Estás seguro de que deseas reservar este coche?")) {
                return;
            }

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    id_coche: idCoche
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    window.location.href = 'pagina_principal.php'; // Redirige al catálogo después de reservar
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al realizar la reserva.');
            });
        }

        // Animaciones al cargar
        document.addEventListener("DOMContentLoaded", () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("visible");
                    }
                });
            });

            const elements = document.querySelectorAll(".animate");
            elements.forEach((el) => observer.observe(el));
        });
    </script>
</body>
</html>