<?php
session_start();
require_once 'db_conexion.php';

// Filtros
$where = [];
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
    <title>Coches en Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/pagina_principal.css">
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
    </script>
</head>
<body>
<header>
    <div class="logo">
        <img src="logo_blanco.png" alt="Logo">
    </div>
    <nav>
        <ul>
            <li><a href="#">Inicio</a></li>
            <li><a href="#">Quiénes somos</a></li>
            <li><a href="#">Servicios</a></li>
            <li><a href="#">Vehículos</a></li>
            <li><a href="#">Contacto</a></li>
        </ul>
    </nav>
    <div class="user-icon">
        <i class='bx bxs-user-circle'></i>
        <?php if (isset($_SESSION['id_cliente']) && isset($_SESSION['nombre_cliente'])): ?>
            <span style="margin-left: 10px; font-size: 14px;"><?= htmlspecialchars($_SESSION['nombre_cliente']) ?></span>
            <a href="logout.php" style="margin-left: 10px; font-size: 12px;">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php" style="margin-left: 10px; font-size: 12px;">Iniciar sesión</a> /
            <a href="registrar.php" style="font-size: 12px;">Registrar</a>
        <?php endif; ?>
    </div>
</header>

<section class="titulo">
    <h1 class="heading-explore">Explora nuestros coches</h1>
</section>

<!-- Formulario de Filtros -->
<form method="GET" style="max-width: 900px; margin: 30px auto; display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
    <input type="text" name="marca" placeholder="Marca" value="<?= isset($_GET['marca']) ? htmlspecialchars($_GET['marca']) : '' ?>">
    <input type="text" name="modelo" placeholder="Modelo" value="<?= isset($_GET['modelo']) ? htmlspecialchars($_GET['modelo']) : '' ?>">
    <input type="number" name="km" placeholder="KM máx" value="<?= isset($_GET['km']) ? htmlspecialchars($_GET['km']) : '' ?>">
    <input type="number" step="0.01" name="precio" placeholder="Precio máx" value="<?= isset($_GET['precio']) ? htmlspecialchars($_GET['precio']) : '' ?>">
    <select name="combustible" style="margin-left : 7px;">
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
    <label>
        <input class="solo_fav" type="checkbox" name="solo_favoritos" <?= isset($_GET['solo_favoritos']) ? 'checked' : '' ?>> Solo favoritos
    </label>
    <div class="filtros">
    <button type="submit" style="padding: 8px 15px; background: black; color: white; border: none; border-radius: 5px; font-size:15px;">Filtrar</button>
    <a href="pagina_principal.php" style="padding: 8px 15px; background: #E5E5E5; color: black; text-decoration: none; font-size:15px; border-radius: 5px; margin-left: 10px;">Limpiar filtros</a>   
    </div>
    
    <!-- Botón para limpiar filtros -->
</form>


<!-- Vehículos -->
<div class="vehiculos-container">
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
            <h3><?= htmlspecialchars($row['marca'] . ' ' . $row['modelo']) ?></h3>
            <p class="precio"><?= number_format($row['precio'], 3) ?> €</p>

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

</body>
</html>
