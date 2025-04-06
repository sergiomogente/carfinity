<?php
session_start();
require_once 'db_conexion.php'; // Importamos la conexión a la base de datos

// Verificamos si el usuario está logueado
if (isset($_SESSION['id_cliente'])) {
    $id_cliente = $_SESSION['id_cliente']; // ID del cliente (usuario logueado)

    // Consulta para obtener los coches favoritos del usuario
    $sql = "SELECT c.id_coche, c.marca, c.modelo, c.precio, c.km, c.combustible, c.transmision, c.foto_portada
            FROM coche c
            JOIN favoritos f ON c.id_coche = f.id_coche
            WHERE f.id_usuario = ? ORDER BY f.id_coche DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "<p>Debes iniciar sesión para ver tus vehículos destacados.</p>";
    exit; // Salir si el usuario no está logueado
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vehículos Destacados - Carfinity</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/pagina_principal_destacados.css">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>

  <header>
    <div class="logo">
      <img src="logo_blanco.png" alt="Carfinity Logo">
    </div>
    <nav>
      <ul>
        <li><a href="#">Quiénes somos</a></li>
        <li><a href="#">Servicios</a></li>
        <li><a href="vehiculos.php">Vehículos</a></li>
        <li><a href="#">Contacto</a></li>
      </ul>
    </nav>
    <div class="user-icon">
      <i class="bx bx-user"></i>
    </div>
  </header>

  <div class="titulo">
    <div class="heading-explore">Vehículos destacados</div>
    <div class="text-wrapper">
      <a href="vehiculos.php">Ver todos<i class='bx bx-up-arrow-alt bx-rotate-90'></i></a>
    </div>       
  </div>

  <div class="tres">
    <ul>
        <li><a href="pagina_principal.php">Vehículos recientes</a></li>
        <li><a href="pagina_principal_destacados.php">Vehículos destacados</a></li>
        <li><a href="pagina_principal_populares.php">Vehículos populares</a></li>
    </ul>
  </div>

  <div class="vehiculos-container">
    <!-- Aquí se cargarán los vehículos o el mensaje si no hay -->
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='vehiculo-card' data-id-coche='{$row['id_coche']}'>
                    <button class='guardar-btn' data-id-coche='{$row['id_coche']}'><i class='bx bxs-bookmark'></i></button>";
            if (!empty($row['foto_portada'])) {
                $imgData = base64_encode($row['foto_portada']);
                echo "<img src='data:image/jpeg;base64,{$imgData}' alt='{$row['marca']} {$row['modelo']}'>";
            } else {
                echo "<img src='assets/img/coches/default.jpg' alt='Imagen no disponible'>";
            }
            echo "<h3>{$row['marca']} {$row['modelo']}</h3>
                  <p class='precio'>{$row['precio']} €</p>
                  <div class='specs-container'>
                      <div class='spec-item'><i class='bx bx-tachometer'></i> <span>{$row['km']} km</span></div>
                      <div class='spec-item'><i class='bx bx-gas-pump'></i> <span>{$row['combustible']}</span></div>
                      <div class='spec-item'><i class='bx bx-cog'></i> <span>{$row['transmision']}</span></div>
                  </div>
                  <a href='detalle_coche.php?id={$row['id_coche']}' class='ver-mas'>Ver más</a>
                </div>";
        }
    } else {
        echo "<p class='mensaje-sin-favoritos'>No tienes vehículos destacados.</p>";
    }
    ?>
  </div>

  <script>
document.addEventListener("DOMContentLoaded", function () {
    const botonesGuardar = document.querySelectorAll(".guardar-btn");

    botonesGuardar.forEach(boton => {
        boton.addEventListener("click", function () {
            const idCoche = this.dataset.idCoche;
            const botonGuardar = this;

            fetch('guardar_favorito.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_coche=${idCoche}&origen=destacados`
            })

            .then(response => response.text())
            .then(data => {
                console.log("Respuesta del servidor:", data);

                if (data.includes("agregado")) {
                    // Agregado a favoritos → actualizar icono pero NO eliminar
                    botonGuardar.classList.add("guardado");
                    botonGuardar.innerHTML = "<i class='bx bxs-bookmark'></i>";
                } else if (data.includes("eliminado")) {
                    // Eliminado de favoritos → eliminar visualmente de la página destacados
                    const card = botonGuardar.closest(".vehiculo-card");
                    if (card) {
                        card.remove();
                    }

                    // Verificar si no quedan coches en la vista
                    const cardsRestantes = document.querySelectorAll(".vehiculo-card");
                    if (cardsRestantes.length === 0) {
                        const contenedor = document.querySelector(".vehiculos-container");
                        const mensaje = document.createElement("p");
                        mensaje.textContent = "No tienes vehículos destacados.";
                        contenedor.appendChild(mensaje);
                    }
                }
            })
            .catch(error => {
                console.error("Error en la solicitud AJAX:", error);
            });
        });
    });
});
</script>


</body>
</html>
