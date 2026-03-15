<?php

// Datos de configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '1234'); // Ajustado a la contraseña estándar de la PC de Gerald
define('DB_NAME', 'sistema_contable');

// Nombres de sitio
define('SITENAME', 'Sistema Contable');

// Generar URL Base automáticamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Obtenemos la ruta del script
$script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); 

// FORZAMOS que la URL base siempre termine en public/ si no lo tiene
if (substr($script_path, -7) !== '/public' && $script_path !== '/public') {
    // Si la ruta termina en / (como suele pasar en la raíz), quitamos la barra y ponemos /public/
    $base_url = $protocol . '://' . $host . rtrim($script_path, '/') . '/public/';
} else {
    $base_url = $protocol . '://' . $host . $script_path . '/';
}

define('BASE_URL', $base_url);