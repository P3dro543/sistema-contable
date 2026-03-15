<?php
// Requerir el archivo de configuración
require_once 'config/config.php';

// Cargar clases core
require_once 'config/Core.php';
require_once 'config/Controller.php';
require_once 'config/Database.php';

// Autoload de modelos o clases adicionales (si fuera necesario)
spl_autoload_register(function ($className) {
    if (file_exists('../app/models/' . $className . '.php')) {
        require_once '../app/models/' . $className . '.php';
    }
});
