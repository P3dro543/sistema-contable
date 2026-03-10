<?php
// Requerir el archivo de inicialización principal
require_once '../app/init.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Emular mod_rewrite para el servidor interno de PHP
if (!isset($_GET['url']) && isset($_SERVER['REQUEST_URI'])) {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = ltrim($uri, '/');
    if (!empty($uri)) {
        $_GET['url'] = $uri;
    }
}

// Inicializar la clase Core Router
$init = new Core();
