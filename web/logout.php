<?php
session_start();

// Elimina todas las variables de sesión
$_SESSION = [];

// Destruye la sesión
session_destroy();

// Redirige al usuario al login con un mensaje (opcional con GET)
header("Location: pagina_principal.php?logout=1");
exit;
