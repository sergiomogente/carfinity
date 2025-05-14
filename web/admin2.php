<?php
session_start();


$conn = new mysqli("localhost", "root", "", "carfinity");
if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}

// Obtener datos para las estadísticas
$total_coches = $conn->query("SELECT COUNT(*) AS total FROM coche")->fetch_assoc()['total'];
$total_clientes = $conn->query("SELECT COUNT(*) AS total FROM cliente")->fetch_assoc()['total'];
$total_reservas = $conn->query("SELECT COUNT(*) AS total FROM reserva")->fetch_assoc()['total'];
$total_mensajes = $conn->query("SELECT COUNT(*) AS total FROM mensaje")->fetch_assoc()['total'];

// Obtener lista de coches
$coches = $conn->query("SELECT * FROM coche ORDER BY id_coche DESC");

// Obtener lista de clientes
$clientes = $conn->query("SELECT * FROM cliente ORDER BY id_cliente DESC");

// Obtener lista de reservas
$reservas = $conn->query("SELECT r.id_reserva, c.nombre AS cliente, coche.marca, coche.modelo, r.fecha 
                          FROM reserva r 
                          JOIN cliente c ON r.id_cliente = c.id_cliente 
                          JOIN coche ON r.id_coche = coche.id_coche 
                          ORDER BY r.fecha DESC");
// Obtener chats
$chats = $conn->query("SELECT DISTINCT id_chat, remitente FROM mensaje WHERE remitente != 'Administrador' AND id_chat != 0 ORDER BY id_chat DESC");
if (!$chats) {
    die("Error al obtener los chats: " . $conn->error);
}

// Obtener mensajes si hay un chat seleccionado
$mensajes_chat = null;
$id_chat = null;
if (isset($_GET['id_chat'])) {
    $id_chat = intval($_GET['id_chat']);
    $mensajes_chat = $conn->query("SELECT remitente, contenido, fecha FROM mensaje WHERE id_chat = $id_chat ORDER BY fecha ASC");
    if (!$mensajes_chat) {
        die("Error al obtener los mensajes: " . $conn->error);
    }
}

// Enviar mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_chat']) && isset($_POST['mensaje_admin'])) {
    $id_chat = intval($_POST['id_chat']);
    $mensaje = $conn->real_escape_string($_POST['mensaje_admin']);
    $remitente = 'Administrador';

    $stmt = $conn->prepare("INSERT INTO mensaje (id_chat, remitente, contenido, fecha) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $id_chat, $remitente, $mensaje);
    $success = $stmt->execute();

    echo json_encode(['status' => $success ? 'success' : 'error']);
    exit();
}

// Obtener mensajes por AJAX
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_messages') {
    $id_chat = intval($_GET['id_chat']);
    $result = $conn->query("SELECT remitente, contenido, fecha FROM mensaje WHERE id_chat = $id_chat ORDER BY fecha ASC");

    $mensajes = [];
    while ($row = $result->fetch_assoc()) {
        $mensajes[] = $row;
    }

    echo json_encode($mensajes);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Carfinity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            width: 250px;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar .active {
            background-color: #495057;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
        .chat-box {
            overflow-y: auto;
            height: 300px;
        }
        .bubble {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 10px;
        }
        .bubble.admin {
            background-color: #d1e7dd;
            text-align: right;
        }
        .bubble.user {
            background-color: #b3b3b3;
            text-align: left;
        }
        .section {
            display: none; /* Ocultar todas las secciones por defecto */
        }
        .section.active {
            display: block; /* Mostrar solo la sección activa */
        }
        .carousel-item img {
    max-height: 400px;
    object-fit: cover;
}
    </style>
</head>
<body>
    <!-- Barra lateral -->
    <div class="sidebar">
        <h3 class="text-center">Carfinity</h3>
        <a href="#" class="nav-link active" data-section="estadisticas">Estadísticas</a>
        <a href="#" class="nav-link" data-section="vehiculos">Gestión de Coches</a>
        <a href="#" class="nav-link" data-section="clientes">Gestión de Clientes</a>
        <a href="#" class="nav-link" data-section="reservas">Gestión de Reservas</a>
        <a href="#" class="nav-link" data-section="mensajes">Mensajes</a>
        <a href="#" class="nav-link" data-section="imagenes">Gestión de Imágenes</a>
        <a href="logout.php" class="text-danger">Cerrar Sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <!-- Estadísticas -->
        <section id="estadisticas" class="section active">
            <h2 class="mb-4">Estadísticas</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total de Coches</h5>
                            <p class="card-text display-4"><?= $total_coches ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total de Clientes</h5>
                            <p class="card-text display-4"><?= $total_clientes ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total de Reservas</h5>
                            <p class="card-text display-4"><?= $total_reservas ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total de Mensajes</h5>
                            <p class="card-text display-4"><?= $total_mensajes ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Gráfico de estadísticas -->
            <canvas id="estadisticasChart" width="400" height="200"></canvas>
        </section>

        <!-- Botón para abrir el modal de creación -->
        <section class="mb-5 section" id="vehiculos">
            <h2 class="mb-4">Gestión de Coches</h2>
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createModal">Agregar Coche</button>

            <!-- Tabla de Coches -->
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Precio</th>
                        <th>Kilómetros</th>
                        <th>Combustible</th>
                        <th>Transmisión</th>
                        <th>Características</th>
                        <th>Color</th>
                        <th>Año</th>
                        <th>Reservado</th>
                        <th>Foto de Portada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($coche = $coches->fetch_assoc()): ?>
                        <tr>
                            <td><?= $coche['id_coche'] ?></td>
                            <td><?= $coche['marca'] ?></td>
                            <td><?= $coche['modelo'] ?></td>
                            <td><?= number_format($coche['precio'], 3) ?> €</td>
                            <td><?= $coche['km'] ?></td>
                            <td><?= $coche['combustible'] ?></td>
                            <td><?= $coche['transmision'] ?></td>
                            <td><?= $coche['caracterisitcas'] ?></td>
                            <td><?= $coche['color'] ?></td>
                            <td><?= $coche['año'] ?></td>
                            <td><?= $coche['reservado'] ? 'Sí' : 'No' ?></td>
                            <td>
                                <?php if (!empty($coche['foto_portada'])): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($coche['foto_portada']) ?>" alt="Foto de Portada" style="width: 100px; height: auto;">
                                <?php else: ?>
                                    No disponible
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" 
                                        data-id="<?= $coche['id_coche'] ?>" 
                                        data-marca="<?= $coche['marca'] ?>" 
                                        data-modelo="<?= $coche['modelo'] ?>" 
                                        data-precio="<?= $coche['precio'] ?>"
                                        data-km="<?= $coche['km'] ?>"
                                        data-combustible="<?= $coche['combustible'] ?>"
                                        data-transmision="<?= $coche['transmision'] ?>"
                                        data-caracterisitcas="<?= $coche['caracterisitcas'] ?>"
                                        data-color="<?= $coche['color'] ?>"
                                        data-año="<?= $coche['año'] ?>"
                                        data-reservado="<?= $coche['reservado'] ?>">
                                    Editar
                                </button>
                                <a href="eliminarVehiculo.php?id=<?= $coche['id_coche'] ?>" class="btn btn-danger btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Modal para crear coche -->
        <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="crearVehiculo.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createModalLabel">Agregar Coche</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="create-marca" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="create-marca" name="marca" required>
                            </div>
                            <div class="mb-3">
                                <label for="create-modelo" class="form-label">Modelo</label>
                                <input type="text" class="form-control" id="create-modelo" name="modelo" required>
                            </div>
                            <div class="mb-3">
                                <label for="create-precio" class="form-label">Precio</label>
                                <input type="number" step="0.01" class="form-control" id="create-precio" name="precio" required>
                            </div>
                            <div class="mb-3">
                                <label for="create-km" class="form-label">Kilómetros</label>
                                <input type="number" step="0.01" class="form-control" id="create-km" name="km">
                            </div>
                            <div class="mb-3">
                                <label for="create-combustible" class="form-label">Combustible</label>
                                <input type="text" class="form-control" id="create-combustible" name="combustible" required>
                            </div>
                            <div class="mb-3">
                                <label for="create-transmision" class="form-label">Transmisión</label>
                                <input type="text" class="form-control" id="create-transmision" name="transmision" required>
                            </div>
                            <div class="mb-3">
                                <label for="create-caracterisitcas" class="form-label">Características</label>
                                <textarea class="form-control" id="create-caracterisitcas" name="caracterisitcas" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="create-color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="create-color" name="color" required>
                            </div>
                            <div class="mb-3">
                                <label for="create-año" class="form-label">Año</label>
                                <input type="text" class="form-control" id="create-año" name="año" required>
                            </div>
                            <div class="mb-3">
                                <label for="create-reservado" class="form-label">Reservado</label>
                                <select class="form-control" id="create-reservado" name="reservado">
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="create-foto" class="form-label">Foto de Portada</label>
                                <input type="file" class="form-control" id="create-foto" name="foto_portada" accept="image/*" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Agregar Coche</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para editar coche -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="actualizarVehiculo.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Editar Coche</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_coche" id="edit-id">
                            <div class="mb-3">
                                <label for="edit-marca" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="edit-marca" name="marca" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-modelo" class="form-label">Modelo</label>
                                <input type="text" class="form-control" id="edit-modelo" name="modelo" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-precio" class="form-label">Precio</label>
                                <input type="number" step="0.01" class="form-control" id="edit-precio" name="precio" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-km" class="form-label">Kilómetros</label>
                                <input type="number" step="0.01" class="form-control" id="edit-km" name="km">
                            </div>
                            <div class="mb-3">
                                <label for="edit-combustible" class="form-label">Combustible</label>
                                <input type="text" class="form-control" id="edit-combustible" name="combustible" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-transmision" class="form-label">Transmisión</label>
                                <input type="text" class="form-control" id="edit-transmision" name="transmision" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-caracterisitcas" class="form-label">Características</label>
                                <textarea class="form-control" id="edit-caracterisitcas" name="caracterisitcas" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="edit-color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="edit-color" name="color" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-año" class="form-label">Año</label>
                                <input type="text" class="form-control" id="edit-año" name="año" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit-reservado" class="form-label">Reservado</label>
                                <select class="form-control" id="edit-reservado" name="reservado">
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit-foto" class="form-label">Foto de Portada</label>
                                <input type="file" class="form-control" id="edit-foto" name="foto_portada" accept="image/*">
                                <small class="text-muted">Deja este campo vacío si no deseas cambiar la imagen.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabla de Clientes -->
        <section class="mb-5 section" id="clientes">
            <h2 class="mb-4">Gestión de Clientes</h2>
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Es Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cliente = $clientes->fetch_assoc()): ?>
                        <tr>
                            <td><?= $cliente['id_cliente'] ?></td>
                            <td><?= $cliente['nombre'] ?></td>
                            <td><?= $cliente['email'] ?></td>
                            <td><?= $cliente['es_admin'] ? 'Sí' : 'No' ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Tabla de Reservas -->
        <section class="mb-5 section" id="reservas">
            <h2 class="mb-4">Gestión de Reservas</h2>
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Vehículo</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($reserva = $reservas->fetch_assoc()): ?>
                        <tr>
                            <td><?= $reserva['id_reserva'] ?></td>
                            <td><?= $reserva['cliente'] ?></td>
                            <td><?= $reserva['marca'] . ' ' . $reserva['modelo'] ?></td>
                            <td><?= $reserva['fecha'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
 <!-- SECCIÓN MENSAJES -->
  <section id="mensajes" class="mt-5 section">
    <h2>Mensajes</h2>
    <div class="row">
      <div class="col-md-4">
        <ul class="list-group">
          <?php while ($chat = $chats->fetch_assoc()): ?>
            <li class="list-group-item">
              <a href="#" class="chat-link" data-id-chat="<?= $chat['id_chat'] ?>">
                <?= $chat['remitente'] ?>
              </a>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>
      <div class="col-md-8">
        <div class="card" style="height: 400px; display: flex; flex-direction: column;">
          <div class="chat-box flex-grow-1 p-3" style="overflow-y: auto;"></div>
          <form id="formChat" class="d-flex p-3 border-top">
            <input type="hidden" name="id_chat" value="<?= $id_chat ?>">
            <input type="text" name="mensaje_admin" class="form-control me-2" placeholder="Escribe un mensaje...">
            <button type="submit" class="btn btn-primary">Enviar</button>
          </form>
        </div>
      </div>
    </div>
  </section>
</main>
 <!-- Gestión de Imágenes -->
<section id="imagenes" class="section">
    <h2 class="mb-4">Gestión de Imágenes de Vehículos</h2>
    <form action="subirImagenes.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="id_coche" class="form-label">Seleccionar Vehículo</label>
            <select class="form-control" id="id_coche" name="id_coche" required>
                <?php
                $coches = $conn->query("SELECT id_coche, marca, modelo FROM coche");
                while ($coche = $coches->fetch_assoc()) {
                    echo "<option value='{$coche['id_coche']}'>{$coche['marca']} {$coche['modelo']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="imagenes" class="form-label">Subir Imágenes</label>
            <input type="file" class="form-control" id="imagenes" name="imagenes[]" multiple accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary">Subir Imágenes</button>
    </form>

    <hr>

    <h3 class="mt-4">Imágenes por Vehículo</h3>
    <div id="carruselImagenes">
        <?php
        $coches = $conn->query("SELECT id_coche, marca, modelo FROM coche");
        while ($coche = $coches->fetch_assoc()) {
            $id_coche = $coche['id_coche'];
            $imagenes = $conn->query("SELECT id_imagen, ruta_imagen FROM imagenes_coche WHERE id_coche = $id_coche");
            if ($imagenes->num_rows > 0) {
                echo "<h4>{$coche['marca']} {$coche['modelo']}</h4>";
                echo "<div id='carousel{$id_coche}' class='carousel slide mb-4' data-bs-ride='carousel'>";
                echo "<div class='carousel-inner'>";
                $active = true;
                while ($imagen = $imagenes->fetch_assoc()) {
                    $activeClass = $active ? 'active' : '';
                    echo "<div class='carousel-item $activeClass'>";
                    echo "<img src='{$imagen['ruta_imagen']}' class='d-block w-100' alt='Imagen del vehículo'>";
                    echo "<form action='eliminarImagen.php' method='POST' class='mt-2'>";
                    echo "<input type='hidden' name='id_imagen' value='{$imagen['id_imagen']}'>";
                    echo "<input type='hidden' name='ruta_imagen' value='{$imagen['ruta_imagen']}'>";
                    echo "<button type='submit' class='btn btn-danger btn-sm'>Eliminar</button>";
                    echo "</form>";
                    echo "</div>";
                    $active = false;
                }
                echo "</div>";
                echo "<button class='carousel-control-prev' type='button' data-bs-target='#carousel{$id_coche}' data-bs-slide='prev'>";
                echo "<span class='carousel-control-prev-icon' aria-hidden='true'></span>";
                echo "<span class='visually-hidden'>Anterior</span>";
                echo "</button>";
                echo "<button class='carousel-control-next' type='button' data-bs-target='#carousel{$id_coche}' data-bs-slide='next'>";
                echo "<span class='carousel-control-next-icon' aria-hidden='true'></span>";
                echo "<span class='visually-hidden'>Siguiente</span>";
                echo "</button>";
                echo "</div>";
            }
        }
        ?>
    </div>
</section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const ctx = document.getElementById('estadisticasChart').getContext('2d');
        const estadisticasChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Coches', 'Clientes', 'Reservas', 'Mensajes'],
                datasets: [{
                    label: 'Estadísticas',
                    data: [<?= $total_coches ?>, <?= $total_clientes ?>, <?= $total_reservas ?>, <?= $total_mensajes ?>],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
    <script>
        // Script para rellenar el modal con los datos del coche seleccionado
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('edit-id').value = button.getAttribute('data-id');
            document.getElementById('edit-marca').value = button.getAttribute('data-marca');
            document.getElementById('edit-modelo').value = button.getAttribute('data-modelo');
            document.getElementById('edit-precio').value = button.getAttribute('data-precio');
            document.getElementById('edit-km').value = button.getAttribute('data-km');
            document.getElementById('edit-combustible').value = button.getAttribute('data-combustible');
            document.getElementById('edit-transmision').value = button.getAttribute('data-transmision');
            document.getElementById('edit-caracterisitcas').value = button.getAttribute('data-caracterisitcas');
            document.getElementById('edit-color').value = button.getAttribute('data-color');
            document.getElementById('edit-año').value = button.getAttribute('data-año');
            document.getElementById('edit-reservado').value = button.getAttribute('data-reservado');
        });
        // Enviar mensaje
document.getElementById('formChat')?.addEventListener('submit', function (e) {
  e.preventDefault();
  const form = e.target;
  const data = new URLSearchParams(new FormData(form));
  fetch('admin2.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: data.toString()
  }).then(res => res.json())
    .then(res => {
      if (res.status === 'success') {
        form.mensaje_admin.value = '';
        cargarMensajes(form.id_chat.value);
      }
    });
});

// Cargar mensajes
document.querySelectorAll('.chat-link').forEach(link => {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    const idChat = this.getAttribute('data-id-chat');
    cargarMensajes(idChat);
    document.querySelector('input[name="id_chat"]').value = idChat;
  });
});

function cargarMensajes(idChat) {
  fetch(`admin2.php?action=get_messages&id_chat=${idChat}`)
    .then(res => res.json())
    .then(mensajes => {
      const chatBox = document.querySelector('.chat-box');
      chatBox.innerHTML = '';
      mensajes.forEach(msg => {
        const bubble = document.createElement('div');
        bubble.className = `bubble ${msg.remitente === 'Administrador' ? 'admin' : 'user'}`;
        bubble.innerHTML = `<strong>${msg.remitente}:</strong><br>${msg.contenido}<br><small>${msg.fecha}</small>`;
        chatBox.appendChild(bubble);
      });
      chatBox.scrollTop = chatBox.scrollHeight;
    });
}
        // Alternar visibilidad de las secciones
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                // Quitar la clase 'active' de todos los enlaces
                document.querySelectorAll('.nav-link').forEach(nav => nav.classList.remove('active'));

                // Añadir la clase 'active' al enlace actual
                this.classList.add('active');

                // Ocultar todas las secciones
                document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));

                // Mostrar la sección correspondiente
                const sectionId = this.getAttribute('data-section');
                document.getElementById(sectionId).classList.add('active');
            });
        });
    </script>
</body>
</html>