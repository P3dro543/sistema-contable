<?php
/**
 * Controlador: AuthController
 * Maneja login, logout y validación de sesión (AUX1, AUX4).
 */

require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/Usuario.php';
require_once __DIR__ . '/../../app/models/Bitacora.php';

class AuthController {

    private Usuario  $usuarioModel;
    private Bitacora $bitacoraModel;

    public function __construct() {
        $db = Database::getConnection();
        $this->usuarioModel  = new Usuario($db);
        $this->bitacoraModel = new Bitacora($db);
    }

    // ------------------------------------------------------------------ //
    //  GET /auth/login  →  Muestra el formulario de login
    // ------------------------------------------------------------------ //
    public function mostrarLogin(): void {
        // Si ya hay sesión activa, redirigir al inicio
        if (!empty($_SESSION['usuario'])) {
            header('Location: index.php?ruta=bienvenida');
            exit;
        }
        $mensaje = $_SESSION['login_msg'] ?? '';
        unset($_SESSION['login_msg']);
        require_once __DIR__ . '/../../views/auth/login.php';
    }

    // ------------------------------------------------------------------ //
    //  POST /auth/login  →  Procesa el intento de login
    // ------------------------------------------------------------------ //
    public function procesarLogin(): void {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $_SESSION['login_msg'] = ['tipo' => 'danger', 'texto' => 'Usuario y/o contraseña incorrectos.'];
            header('Location: index.php?ruta=login');
            exit;
        }

        $usuario = $this->usuarioModel->buscarPorUsername($username);

        if (!$usuario) {
            $this->registrarIntentoFallido($username);
            $_SESSION['login_msg'] = ['tipo' => 'danger', 'texto' => 'Usuario y/o contraseña incorrectos.'];
            header('Location: index.php?ruta=login');
            exit;
        }

        // Verificar si el usuario está bloqueado (estado = 0)
        if ((int) $usuario['estado'] === 0) {
            $_SESSION['login_msg'] = ['tipo' => 'warning', 'texto' => 'Su cuenta está bloqueada. Contacte al administrador.'];
            header('Location: index.php?ruta=login');
            exit;
        }

        // Verificar contraseña
        if (!password_verify($password, $usuario['password'])) {
            $this->registrarIntentoFallido($username, $usuario['id_usuario']);
            $_SESSION['login_msg'] = ['tipo' => 'danger', 'texto' => 'Usuario y/o contraseña incorrectos.'];
            header('Location: index.php?ruta=login');
            exit;
        }

        // Login exitoso — limpiar intentos fallidos
        unset($_SESSION['intentos_fallidos'][$username]);

        // Cargar roles y pantallas permitidas
        $roles     = $this->usuarioModel->obtenerRoles($usuario['id_usuario']);
        $pantallas = $this->usuarioModel->obtenerPantallas($usuario['id_usuario']);

        $_SESSION['usuario']   = [
            'id'       => $usuario['id_usuario'],
            'username' => $usuario['username'],
            'nombre'   => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'correo'   => $usuario['correo'],
        ];
        $_SESSION['roles']     = array_column($roles, 'nombre');
        $_SESSION['pantallas'] = array_column($pantallas, 'ruta');
        $_SESSION['ultimo_actividad'] = time();

        // Registrar en bitácora
        $this->bitacoraModel->registrar(
            $username,
            'LOGIN',
            ['mensaje' => 'Inicio de sesión exitoso']
        );

        header('Location: index.php?ruta=bienvenida');
        exit;
    }

    // ------------------------------------------------------------------ //
    //  GET /auth/logout  →  Cierra la sesión
    // ------------------------------------------------------------------ //
    public function logout(): void {
        $usuario = $_SESSION['usuario']['username'] ?? 'desconocido';
        $this->bitacoraModel->registrar($usuario, 'LOGOUT', ['mensaje' => 'Cierre de sesión']);
        session_destroy();
        header('Location: index.php?ruta=login');
        exit;
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    private function registrarIntentoFallido(string $username, int $idUsuario = 0): void {
        if (!isset($_SESSION['intentos_fallidos'][$username])) {
            $_SESSION['intentos_fallidos'][$username] = 0;
        }
        $_SESSION['intentos_fallidos'][$username]++;

        $intentos = $_SESSION['intentos_fallidos'][$username];

        $this->bitacoraModel->registrar(
            $username,
            'LOGIN_FALLIDO',
            ['intento' => $intentos]
        );

        if ($intentos >= 3 && $idUsuario > 0) {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE usuarios SET estado = 0 WHERE id_usuario = :id");
            $stmt->execute([':id' => $idUsuario]);

            $this->bitacoraModel->registrar(
                $username,
                'USUARIO_BLOQUEADO',
                ['id_usuario' => $idUsuario, 'motivo' => '3 intentos fallidos de login']
            );
        }
    }

    // ------------------------------------------------------------------ //
    //  Verificación de sesión activa
    // ------------------------------------------------------------------ //

    public static function verificarSesion(): void {
        if (empty($_SESSION['usuario'])) {
            $_SESSION['login_msg'] = ['tipo' => 'info', 'texto' => 'Por favor inicie sesión para utilizar el sistema.'];
            header('Location: index.php?ruta=login');
            exit;
        }

        $limite = 300;
        if (isset($_SESSION['ultimo_actividad']) && (time() - $_SESSION['ultimo_actividad']) > $limite) {
            $usuario = $_SESSION['usuario']['username'] ?? 'desconocido';
            $db = Database::getConnection();
            $bitacora = new Bitacora($db);
            $bitacora->registrar($usuario, 'SESION_EXPIRADA', ['motivo' => 'Inactividad de 5 minutos']);

            session_destroy();
            session_start();
            $_SESSION['login_msg'] = ['tipo' => 'warning', 'texto' => 'Su sesión ha expirado por inactividad.'];
            header('Location: index.php?ruta=login');
            exit;
        }

        $_SESSION['ultimo_actividad'] = time();
    }
}