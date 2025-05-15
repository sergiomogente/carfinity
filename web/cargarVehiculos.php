<?php
require_once 'db_conexion.php';

$coches = $conn->query("SELECT * FROM coche ORDER BY id_coche DESC");

while ($coche = $coches->fetch_assoc()): ?>
    <tr id="vehiculo-<?= $coche['id_coche'] ?>">
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
            <button 
                class="btn btn-danger btn-sm" 
                onclick="eliminarVehiculo(<?= $coche['id_coche'] ?>)">
                Eliminar
            </button>
        </td>
    </tr>
<?php endwhile; ?>