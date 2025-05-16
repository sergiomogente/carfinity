<?php
session_start();
require_once 'db_conexion.php';

// Verificar si el usuario está autenticado
$id_cliente = $_SESSION['id_cliente'] ?? null;
$nombre_cliente = $_SESSION['nombre_cliente'] ?? 'Cliente'; // Nombre del cliente desde la sesión

// Manejar solicitudes AJAX para el chat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    // Enviar mensaje
    $mensaje = $_POST['mensaje'];
    if ($id_cliente && !empty($mensaje)) {
        $stmt = $conn->prepare("INSERT INTO mensaje (id_chat, remitente, contenido, fecha) VALUES (?, ?, ?, NOW())");
        $id_chat = $id_cliente; // Usamos el ID del cliente como ID del chat
        $stmt->bind_param("iss", $id_chat, $nombre_cliente, $mensaje);
        $success = $stmt->execute();
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

// Filtros
$where = ["reservado = 0"]; // Solo mostrar coches no reservados
$params = [];
$types = '';

if (!empty($_GET['marca'])) {
    $where[] = "marca LIKE ?";
    $params[] = '%' . $_GET['marca'] . '%';
    $types .= 's';
}
if (!empty($_GET['modelo'])) {
    $where[] = "modelo LIKE ?";
    $params[] = '%' . $_GET['modelo'] . '%';
    $types .= 's';
}
if (!empty($_GET['km'])) {
    $where[] = "km <= ?";
    $params[] = $_GET['km'];
    $types .= 'i';
}
if (!empty($_GET['precio'])) {
    $where[] = "precio <= ?";
    $params[] = $_GET['precio'];
    $types .= 'd';
}
if (!empty($_GET['combustible'])) {
    $where[] = "combustible = ?";
    $params[] = $_GET['combustible'];
    $types .= 's';
}
if (!empty($_GET['transmision'])) {
    $where[] = "transmision = ?";
    $params[] = $_GET['transmision'];
    $types .= 's';
}
$filtroFavoritos = isset($_GET['solo_favoritos']) && isset($_SESSION['id_cliente']);

$sql = "SELECT c.*, 
            CASE 
                WHEN f.id_coche IS NOT NULL THEN 1 
                ELSE 0 
            END AS es_favorito
        FROM coche c
        LEFT JOIN favoritos f ON c.id_coche = f.id_coche";

// Si se selecciona "Solo favoritos", agrega la condición al WHERE
if ($filtroFavoritos) {
    $where[] = "f.id_usuario = ?";
    $params[] = $_SESSION['id_cliente'];
    $types .= 'i';
}

// Combina los filtros adicionales
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Preparar y ejecutar consulta
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carfinity - Plana Principal</title>
    <link rel="stylesheet" href="assets/css/pagina_principal.css">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/pagina_principal.css">
    <style>
        /* Mensajes del administrador */
.admin-message {
    background-color: #cfcfcf; /* Gris claro */
    color: #333; /* Texto gris oscuro */
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 10px;
    text-align: left;
    align-self: flex-start; /* Alinea a la izquierda */
    max-width: 70%; /* Limita el ancho del mensaje */
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1); /* Sombra para destacar */
}

/* Mensajes del usuario */
.user-message {
    background-color: #bfbfbf; /* Gris más oscuro */
    color: #030303; /* Texto blanco */
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 10px;
    text-align: right;
    align-self: flex-end; /* Alinea a la derecha */
    max-width: 70%; /* Limita el ancho del mensaje */
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1); /* Sombra para destacar */
}

/* Contenedor de mensajes */
.chat-messages {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 300px;
    overflow-y: auto;
    padding: 10px;
    background-color: #eaeaea; /* Fondo gris claro */
    border-radius: 10px;
    border: 1px solid #ddd;
}
/* Botón flotante */
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

/* Ventana emergente del chat */
.chat-popup {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 300px;
    background-color: #f5f5f5; /* Fondo gris claro */
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: none;
    flex-direction: column;
    z-index: 1000;
}

/* Encabezado del chat */
.chat-header {
    background-color: #d6d6d6; /* Gris medio */
    color: #333; /* Texto gris oscuro */
    padding: 10px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Formulario del chat */
.chat-form {
    display: flex;
    border-top: 1px solid #ddd;
    background-color: #f5f5f5; /* Fondo gris claro */
}

.chat-form input {
    flex: 1;
    border: none;
    padding: 10px;
    font-size: 14px;
    background-color: #eaeaea; /* Fondo gris claro */
    color: #333; /* Texto gris oscuro */
}

.chat-form button {
    background-color: #bfbfbf; /* Gris más oscuro */
    color: white;
    border: none;
    padding: 10px;
    cursor: pointer;
}

</style>
    <script>
        function toggleFavorito(idCoche, btn) {
            const formData = new FormData();
            formData.append('id_coche', idCoche);

            fetch('guardar_favorito.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.action === 'agregado') {
                        btn.classList.add('guardado');
                        btn.querySelector('i').classList.replace('bx-heart', 'bxs-heart');
                    } else if (data.action === 'eliminado') {
                        btn.classList.remove('guardado');
                        btn.querySelector('i').classList.replace('bxs-heart', 'bx-heart');
                    }
                } else {
                    console.error(data.message);
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error al procesar la solicitud:', err);
                alert('Ocurrió un error. Inténtalo de nuevo.');
            });
        }

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
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const chatButton = document.getElementById('chat-button');
        const chatPopup = document.getElementById('chat-popup');
        const closeChat = document.getElementById('close-chat');
        const chatMessages = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');

        // Abrir el chat
        chatButton.addEventListener('click', () => {
            chatPopup.style.display = 'flex';
            cargarMensajes();
        });

        // Cerrar el chat
        closeChat.addEventListener('click', () => {
            chatPopup.style.display = 'none';
        });

        // Enviar mensaje
        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const mensaje = chatInput.value.trim();
            if (mensaje) {
                enviarMensaje(mensaje);
                chatInput.value = '';
            }
        });

        // Función para cargar mensajes
        function cargarMensajes() {
            fetch('?action=get_messages')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        chatMessages.innerHTML = ''; // Limpiar mensajes anteriores
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
                        chatMessages.scrollTop = chatMessages.scrollHeight; // Desplazar al final
                    } else {
                        console.error('Error al cargar mensajes:', data.message);
                    }
                })
                .catch(error => console.error('Error al cargar mensajes:', error));
        }

        // Función para enviar mensaje
        function enviarMensaje(mensaje) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `mensaje=${encodeURIComponent(mensaje)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cargarMensajes(); // Recargar mensajes después de enviar
                } else {
                    alert('Error al enviar el mensaje.');
                }
            })
            .catch(error => console.error('Error al enviar mensaje:', error));
        }

        
        setInterval(cargarMensajes, 300);
    });
</script>
</head>
<body>
<header class="animate">
        <div class="logo">
            <img src="logo_blanco.png" alt="Logo">
        </div>
        <nav>
            <ul>
                <li><a href="pagina_principal.php">Inicio</a></li>
                <li><a href="quien_somos.php" class="active">Quiénes somos</a></li>
                <li><a href="servicios.php">Servicios</a></li>
                <li><a href="contacto.php">Contacto</a></li>
            </ul>
        </nav>
        <div class="user-icon">
            <i class='bx bxs-user-circle'></i>
            <?php if (isset($_SESSION['id_cliente']) && isset($_SESSION['nombre_cliente'])): ?>
                <span><?= htmlspecialchars($_SESSION['nombre_cliente']) ?></span>
                <a href="logout.php"><i class='bx bx-log-out'></i>
                </a>
            <?php else: ?>
                <a href="login.php">Iniciar sesión</a> /
                <a href="registrar.php">Registrar</a>
            <?php endif; ?>
        </div>
    </header>

<section class="titulo animate">
    <h1 class="heading-explore">Explora nuestros coches</h1>
</section>

<!-- Formulario de Filtros -->
<form class="animate" method="GET" style="max-width: 900px; margin: 30px auto; display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
    <input type="text" name="marca" placeholder="Marca" value="<?= isset($_GET['marca']) ? htmlspecialchars($_GET['marca']) : '' ?>">
    <input type="text" name="modelo" placeholder="Modelo" value="<?= isset($_GET['modelo']) ? htmlspecialchars($_GET['modelo']) : '' ?>">
    <input type="number" name="km" placeholder="KM máx" value="<?= isset($_GET['km']) ? htmlspecialchars($_GET['km']) : '' ?>">
    <input type="number" step="0.01" name="precio" placeholder="Precio máx" value="<?= isset($_GET['precio']) ? htmlspecialchars($_GET['precio']) : '' ?>">
    <select name="combustible">
        <option value="">Combustible</option>
        <option value="Gasolina" <?= isset($_GET['combustible']) && $_GET['combustible'] === 'Gasolina' ? 'selected' : '' ?>>Gasolina</option>
        <option value="Diésel" <?= isset($_GET['combustible']) && $_GET['combustible'] === 'Diésel' ? 'selected' : '' ?>>Diésel</option>
        <option value="Híbrido" <?= isset($_GET['combustible']) && $_GET['combustible'] === 'Híbrido' ? 'selected' : '' ?>>Híbrido</option>
        <option value="Eléctrico" <?= isset($_GET['combustible']) && $_GET['combustible'] === 'Eléctrico' ? 'selected' : '' ?>>Eléctrico</option>
    </select>
    <select name="transmision">
        <option value="">Transmisión</option>
        <option value="Manual" <?= isset($_GET['transmision']) && $_GET['transmision'] === 'Manual' ? 'selected' : '' ?>>Manual</option>
        <option value="Automático" <?= isset($_GET['transmision']) && $_GET['transmision'] === 'Automático' ? 'selected' : '' ?>>Automático</option>
    </select>
    <div class="solo_fav">
    <label>
        <input type="checkbox" name="solo_favoritos" <?= isset($_GET['solo_favoritos']) ? 'checked' : '' ?>> Solo favoritos
    </label>
    </div>
    
    <div style="display: flex; gap: 10px; margin-top: 10px;">
        <button type="submit" class="btn-filtrar">Filtrar</button>
        <a href="pagina_principal.php" class="btn-limpiar">Limpiar filtros</a>
    </div>
</form>


<!-- Vehículos -->
<div class="vehiculos-container animate">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="vehiculo-card">
            <?php
            $imgData = '';
            if (!empty($row['foto_portada'])) {
                $imgData = 'data:image/jpeg;base64,' . base64_encode($row['foto_portada']);
            } else {
                $imgData = 'default.jpg';
            }
            ?>
            <img src="<?= $imgData ?>" alt="Foto vehículo">
            <div class="nombre_precio">
            <h3><?= htmlspecialchars($row['marca'] . ' ' . $row['modelo']) ?></h3>
            <p class="precio"><?= number_format($row['precio'], 3) ?> €</p>
            </div>
            

            <div class="specs-container">
                <div class="spec-item"><i class="bx bx-tachometer"></i> <?= $row['km'] ?> km</div>
                <div class="spec-item"><i class="bx bx-gas-pump"></i> <?= $row['combustible'] ?></div>
                <div class="spec-item"><i class="bx bx-cog"></i> <?= $row['transmision'] ?></div>
            </div>

            <a href="detalle_coche.php?id=<?= $row['id_coche'] ?>" class="ver-mas">Ver más</a>

            <?php if (isset($_SESSION['id_cliente'])): ?>
                <button class="guardar-btn <?= $row['es_favorito'] ? 'guardado' : '' ?>" onclick="toggleFavorito(<?= $row['id_coche'] ?>, this)">
                    <i class="bx <?= $row['es_favorito'] ? 'bxs-heart' : 'bx-heart' ?>"></i>
                </button>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

<?php if ($result->num_rows === 0): ?>
    <div class="mensaje-sin-favoritos">No se encontraron vehículos con esos filtros.</div>
<?php endif; ?>
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
    <div id="chat-messages" class="chat-messages">
        <!-- Aquí se cargarán los mensajes -->
    </div>
    <form id="chat-form" class="chat-form">
        <input type="text" id="chat-input" placeholder="Escribe un mensaje..." required>
        <button type="submit" class="send-btn"><i class="bx bx-send"></i></button>
    </form>
</div>

</footer>
</body>
</html>