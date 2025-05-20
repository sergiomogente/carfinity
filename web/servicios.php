<?php
session_start();
require_once 'db_conexion.php';
// Verificar si el usuario está autenticado
$id_cliente = $_SESSION['id_cliente'] ?? null;
$nombre_cliente = $_SESSION['nombre_cliente'] ?? 'Cliente'; // Nombre del cliente desde la sesión
// Manejar solicitudes AJAX para el chat

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    // Enviar mensaje del usuario
    $mensaje = $_POST['mensaje'];
    if ($id_cliente && !empty($mensaje)) {
        $stmt = $conn->prepare("INSERT INTO mensaje (id_chat, remitente, contenido, fecha) VALUES (?, ?, ?, NOW())");
        $id_chat = $id_cliente; // Usamos el ID del cliente como ID del chat
        $stmt->bind_param("iss", $id_chat, $nombre_cliente, $mensaje);
        $success = $stmt->execute();

        // Comprobar si ya existe un mensaje del admin en este chat
        $checkAdmin = $conn->prepare("SELECT COUNT(*) as total FROM mensaje WHERE id_chat = ? AND remitente = 'Administrador'");
        $checkAdmin->bind_param("i", $id_chat);
        $checkAdmin->execute();
        $result = $checkAdmin->get_result();
        $row = $result->fetch_assoc();

        if ($row['total'] == 0) {
            // Si no hay mensajes del admin, enviar el mensaje automático
            $mensaje_auto = "Hola Buenas, gracias por contactar con Carfinity, en breves momentos te atenderemos la consulta.";
            $stmtAdmin = $conn->prepare("INSERT INTO mensaje (id_chat, remitente, contenido, fecha) VALUES (?, 'Administrador', ?, NOW())");
            $stmtAdmin->bind_param("is", $id_chat, $mensaje_auto);
            $stmtAdmin->execute();
        }

        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al enviar el mensaje.']);
    }
    exit();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_messages') {
    // Obtener mensajes
    if ($id_cliente) {
        $id_chat = $id_cliente; // Usamos el ID del cliente como ID del chat
        $result = $conn->query("SELECT remitente, contenido, DATE_FORMAT(fecha, '%Y-%m-%d %H:%i:%s') AS fecha 
                                FROM mensaje 
                                WHERE id_chat = $id_chat 
                                ORDER BY fecha ASC");
        $mensajes = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'mensajes' => $mensajes]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios - Carfinity</title>
    <link rel="stylesheet" href="assets/css/servicios.css">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <style>
        /* HEADER igual que en principal */
        .header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            min-height: 70px;
        }
        .logo img {
            width: 160px;
            height: auto;
            display: block;
        }
        nav ul {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0 20px 0 0;
            background: #1F1F1F;
            align-items: center;
            justify-content: flex-end;
        }
        .user-icon {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #1F1F1F;
            color: #E5E5E5;
            padding: 10px 20px;
            font-size: 16px;
            border-bottom: 1px solid #222;
            justify-content: center;
            width: auto;
        }

        /* Cards responsive */
        .services-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            margin: 40px 0;
        }
        .service-card {
            width: 320px;
            min-width: 220px;
            background: #232323;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.10);
            overflow: hidden;
            transition: transform 0.2s;
        }
        .service-card:hover {
            transform: translateY(-5px) scale(1.03);
        }

        /* Responsive header y cards */
        @media (max-width: 900px) {
            .header-row {
                max-width: 100vw;
                padding: 0 10px;
            }
            .logo img {
                width: 120px;
            }
            nav ul {
                gap: 15px;
                padding-right: 10px;
            }
        }
        @media (max-width: 600px) {
            .header-row {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
                padding: 0;
            }
            
            .logo {
                display: flex;
                justify-content: center;
                width: 100%;
                margin: 10px 0;
            }
            .logo img {
                width: 120px;
                margin: 0 auto;
            }
            nav {
                width: 100%;
            }
            nav ul {
                flex-direction: column;
                gap: 0;
                background: #1F1F1F;
                width: 100%;
                padding: 0;
                margin: 0;
                position: static;
                border-top: none;
            }
            nav ul li {
                width: 100%;
                text-align: center;
                padding: 15px 0;
                border-bottom: 1px solid #222;
            }
            nav ul li:last-child {
                border-bottom: none;
            }
            .user-icon {
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 16px;
                background: #1F1F1F;
                padding: 10px 0;
                border-bottom: 1px solid #222;
                width: 100%;
                margin: 0;
            }
            .services-container {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }
            .service-card {
                width: 95%;
                min-width: unset;
                margin: 0 auto;
            }
               main.animate, main {
            margin-top: 240px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="animate">
        <div class="header-row">
            <div class="logo">
                <img src="logo_blanco.png" alt="Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="pagina_principal.php">Inicio</a></li>
                    <li><a href="quien_somos.php">Quiénes somos</a></li>
                    <li><a href="servicios.php" class="active">Servicios</a></li>
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
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="animate">
        <h1>Nuestros Servicios</h1>
        <div class="services-container">
            <div class="service-card">
                <div class="card-inner">
                    <div class="card-front">
                        <i class='bx bxs-car-mechanic' aria-hidden="true"></i>
                        <h3>Revisión técnica</h3>
                    </div>
                    <div class="card-back">
                        <p>Realizamos revisiones técnicas completas para garantizar la seguridad y el rendimiento de tu vehículo.</p>
                    </div>
                </div>
            </div>
            <div class="service-card">
                <div class="card-inner">
                    <div class="card-front">
                        <i class='bx bxs-wrench' aria-hidden="true"></i>
                        <h3>Mantenimiento</h3>
                    </div>
                    <div class="card-back">
                        <p>Ofrecemos servicios de mantenimiento y reparación para mantener tu coche en perfectas condiciones.</p>
                    </div>
                </div>
            </div>
            <div class="service-card">
                <div class="card-inner">
                    <div class="card-front">
                        <i class='bx bxs-shield' aria-hidden="true"></i>
                        <h3>Seguros de vehículos</h3>
                    </div>
                    <div class="card-back">
                        <p>Protege tu coche con nuestras opciones de seguros personalizadas y asequibles.</p>
                    </div>
                </div>
            </div>
            <div class="service-card">
                <div class="card-inner">
                    <div class="card-front">
                        <i class='bx bxs-map' aria-hidden="true"></i>
                        <h3>Asistencia en carretera</h3>
                    </div>
                    <div class="card-back">
                        <p>Disfruta de tranquilidad con nuestro servicio de asistencia en carretera disponible las 24 horas.</p>
                    </div>
                </div>
            </div>
        </div>
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

    <!-- Botón flotante para abrir el chat -->
    <div id="chat-button" class="chat-button">
        <i class="bx bx-chat"></i>
    </div>

    <!-- Ventana emergente del chat -->
    <div id="chat-popup" class="chat-popup">
        <div class="chat-header">
            <h4>Chat con el Administrador</h4>
            <button id="close-chat" class="close-chat">&times;</button>
        </div>
        <div id="chat-messages" class="chat-messages"></div>
        <form id="chat-form" class="chat-form">
            <input type="text" id="chat-input" placeholder="Escribe un mensaje..." required>
            <button type="submit" class="send-btn"><i class="bx bx-send"></i></button>
        </form>
    </div>

    <style>
    .chat-button {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #666;
        color: white;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }
    .chat-popup {
        position: fixed;
        bottom: 90px;
        right: 20px;
        width: 300px;
        background-color: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        display: none;
        flex-direction: column;
        z-index: 1000;
    }
    .chat-header {
        background-color: #d6d6d6;
        color: #333;
        padding: 10px;
        border-radius: 10px 10px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .chat-messages {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-height: 300px;
        overflow-y: auto;
        padding: 10px;
        background-color: #eaeaea;
        border-radius: 10px;
        border: 1px solid #ddd;
    }
    .admin-message {
        background-color: #cfcfcf;
        color: #333;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 10px;
        text-align: left;
        align-self: flex-start;
        max-width: 70%;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
    }
    .user-message {
        background-color: #bfbfbf;
        color: #030303;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 10px;
        text-align: right;
        align-self: flex-end;
        max-width: 70%;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
    }
    .chat-form {
        display: flex;
        border-top: 1px solid #ddd;
        background-color: #f5f5f5;
    }
    .chat-form input {
        flex: 1;
        border: none;
        padding: 10px;
        font-size: 14px;
        background-color: #eaeaea;
        color: #333;
    }
    .chat-form button {
        background-color: #bfbfbf;
        color: white;
        border: none;
        padding: 10px;
        cursor: pointer;
    }
    @media (max-width: 600px) {
        .chat-popup {
            width: 98vw;
            right: 1vw;
            bottom: 80px;
            min-width: unset;
            max-width: 100vw;
        }
        .chat-button {
            width: 50px;
            height: 50px;
            font-size: 20px;
            bottom: 15px;
            right: 15px;
        }
        .chat-header {
            flex-direction: column;
            gap: 5px;
            text-align: center;
        }
        .chat-messages {
            max-height: 200px;
            font-size: 14px;
        }
        .chat-form input {
            font-size: 13px;
            padding: 8px;
        }
        .chat-form button {
            padding: 8px;
            font-size: 16px;
        }
    }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Animación de aparición
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("visible");
                    }
                });
            });
            document.querySelectorAll(".animate").forEach((el) => observer.observe(el));

            // Chat funcionalidad
            const chatButton = document.getElementById('chat-button');
            const chatPopup = document.getElementById('chat-popup');
            const closeChat = document.getElementById('close-chat');
            const chatMessages = document.getElementById('chat-messages');
            const chatForm = document.getElementById('chat-form');
            const chatInput = document.getElementById('chat-input');

            chatButton.addEventListener('click', () => {
                chatPopup.style.display = 'flex';
                cargarMensajes();
            });

            closeChat.addEventListener('click', () => {
                chatPopup.style.display = 'none';
            });

            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const mensaje = chatInput.value.trim();
                if (mensaje) {
                    enviarMensaje(mensaje);
                    chatInput.value = '';
                }
            });

            function cargarMensajes() {
                fetch('?action=get_messages')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            chatMessages.innerHTML = '';
                            data.mensajes.forEach(msg => {
                                const messageDiv = document.createElement('div');
                                messageDiv.className = msg.remitente === 'Administrador' ? 'admin-message' : 'user-message';
                                messageDiv.innerHTML = `
                                    <strong>${msg.remitente}:</strong>
                                    <p>${msg.contenido}</p>
                                    <small>${msg.fecha}</small>
                                `;
                                chatMessages.appendChild(messageDiv);
                            });
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                        }
                    });
            }

            function enviarMensaje(mensaje) {
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `mensaje=${encodeURIComponent(mensaje)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cargarMensajes();
                    } else {
                        alert('Error al enviar el mensaje.');
                    }
                });
            }

            setInterval(cargarMensajes, 500);
        });
    </script>
</body>
</html>
