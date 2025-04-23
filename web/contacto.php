<?php
session_start();
require_once 'db_conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Carfinity</title>
    <link rel="stylesheet" href="assets/css/contacto.css">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
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
                <li><a href="contacto.php" class="active">Contacto</a></li>
            </ul>
        </nav>
        <div class="user-icon">
            <i class='bx bxs-user-circle'></i>
            <?php if (isset($_SESSION['id_cliente']) && isset($_SESSION['nombre_cliente'])): ?>
                <span><?= htmlspecialchars($_SESSION['nombre_cliente']) ?></span>
                <a href="logout.php"><i class='bx bx-log-out'></i> Cerrar sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar sesión</a> /
                <a href="registrar.php">Registrar</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="animate">
        <h1>Contáctanos</h1>
        <p>Si tienes alguna pregunta o necesitas más información, no dudes en contactarnos. Completa el formulario a continuación y nos pondremos en contacto contigo lo antes posible.</p>
        
        <form action="procesar_contacto.php" method="POST" class="contact-form">
            <input type="text" name="nombre" placeholder="Tu nombre" required>
            <input type="email" name="email" placeholder="Tu correo electrónico" required>
            <input type="tel" name="telefono" placeholder="Tu teléfono" required>
            <textarea name="mensaje" rows="5" placeholder="Tu mensaje" required></textarea>
            <button type="submit">Enviar</button>
        </form>
    </main>

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
        document.addEventListener("DOMContentLoaded", () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("visible");
                    }
                });
            });

            // Selecciona todos los elementos que quieres animar
            const elements = document.querySelectorAll(".animate");
            elements.forEach((el) => observer.observe(el));
        });
    </script>
</body>
</html>
