<?php
/**
 * index.php — Punto de entrada único del sistema.
 * Actúa como front-controller: lee ?ruta=xxx y despacha al controlador.
 */

// 1. Activar reporte de errores (Crucial para desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ── Iniciar sesión ──────────────────────────────────────────────────────
session_start();

// ── Autoload básico ─────────────────────────────────────────────────────
define('BASE_PATH', __DIR__);

// Verificamos que los archivos existan antes de requerirlos para evitar "Fatal Errors"
$files = [
    '/app/config/Database.php',
    '/app/models/Bitacora.php',
    '/app/models/Usuario.php',
    '/app/models/DireccionTercero.php',
    '/app/controllers/AuthController.php',
    '/app/controllers/DireccionController.php'
];

foreach ($files as $file) {
    if (file_exists(BASE_PATH . $file)) {
        require_once BASE_PATH . $file;
    } else {
        die("Error: No se encontró el archivo fundamental: " . $file);
    }
}

// ── Rutas ────────────────────────────────────────────────────────────────
$ruta = $_GET['ruta'] ?? 'login';

switch ($ruta) {

    // ── AUTH ────────────────────────────────────────────────────────────
    case 'login':
        (new AuthController())->mostrarLogin();
        break;

    case 'login_post':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // CORRECCIÓN: Se quita la "/" inicial para que sea ruta relativa
            header('Location: index.php?ruta=login');
            exit;
        }
        (new AuthController())->procesarLogin();
        break;

    case 'logout':
        (new AuthController())->logout();
        break;

    case 'bienvenida':
        AuthController::verificarSesion();
        $tituloPagina = 'Bienvenida';
        require_once BASE_PATH . '/views/auth/bienvenida.php';
        break;

    // ── DIRECCIONES (AUX11) ──────────────────────────────────────────────
    case 'mov_centro_costo':
        include 'views/reportes/mov_centro_costo.php'; // Aquí incluís el reporte
        break;
    
    
    case 'direcciones':
        (new DireccionController())->index();
        break;

    case 'direcciones_crear':
        (new DireccionController())->crear();
        break;

    case 'direcciones_guardar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?ruta=terceros');
            exit;
        }
        (new DireccionController())->guardar();
        break;

    case 'direcciones_editar':
        (new DireccionController())->editar();
        break;

    case 'direcciones_actualizar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?ruta=terceros');
            exit;
        }
        (new DireccionController())->actualizar();
        break;

    case 'direcciones_eliminar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?ruta=terceros');
            exit;
        }
        (new DireccionController())->eliminar();
        break;

    // ── 404 ──────────────────────────────────────────────────────────────
    default:
        // Solo verificamos sesión si no es una ruta pública
        AuthController::verificarSesion();
        http_response_code(404);
        echo '<div style="font-family:sans-serif;padding:2rem;text-align:center;">';
        echo '<h2>404 — Página no encontrada</h2>';
        echo '<p><a href="index.php?ruta=bienvenida">Volver al inicio</a></p>';
        echo '</div>';
        break;
}