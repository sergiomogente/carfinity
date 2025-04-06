<?php
session_start();
require_once 'db_conexion.php'; // Importamos la conexión a la base de datos

// Consulta a la base de datos
$sql = "SELECT id_coche, marca, modelo, precio, km, combustible, transmision, foto_portada FROM coche ORDER BY id_coche DESC LIMIT 6";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Página Principal - Carfinity</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/pagina_principal.css">
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
    <div class="heading-explore">Vehículos en stock</div>
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
    <?php
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        echo "<div class='vehiculo-card'>
                <button class='guardar-btn' data-id-coche='{$row['id_coche']}'><i class='bx bx-bookmark'></i></button>";
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
      echo "<p>No hay vehículos disponibles en este momento.</p>";
    }
    ?>
  </div>
  <div class="servicios">
    <h2>¿Por qué elegirnos?</h2>
  </div>
  <script>
   document.addEventListener("DOMContentLoaded", function() {
    const botonesGuardar = document.querySelectorAll(".guardar-btn");

    botonesGuardar.forEach(boton => {
        boton.addEventListener("click", function() {
            const idCoche = this.dataset.idCoche; // Obtener el ID del coche desde el atributo data-id
            const botonGuardar = this;

            // Realizar la petición AJAX para guardar el coche
            fetch('guardar_favorito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id_coche=${idCoche}&origen=generico`
            })
            .then(response => response.text())
            .then(data => {
                console.log(data); // Ver el mensaje de la respuesta

                // Cambiar el ícono según si el coche fue guardado o no
                botonGuardar.classList.toggle("guardado");
                if (botonGuardar.classList.contains("guardado")) {
                    botonGuardar.innerHTML = "<i class='bx bxs-bookmark'></i>"; 
                } else {
                    botonGuardar.innerHTML = "<i class='bx bx-bookmark'></i>"; 
                }
            })
            .catch(error => {
                console.log("Error al guardar el coche: ", error);
            });
        });
    });
});

  </script>

</body>
</html>
