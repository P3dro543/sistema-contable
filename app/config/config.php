<?php

// Datos de configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // Ajustado a la contraseña estándar de la PC de Gerald
define('DB_NAME', 'sistema_contable');

// Nombres de sitio
define('SITENAME', 'Sistema Contable');

// Generar URL Base automáticamente según el entorno donde corra
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); // e.g. /sitios/public
$base_url = $protocol . '://' . $host . $script_path . '/';

define('BASE_URL', $base_url);
