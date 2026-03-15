<?php
class AuthController extends Controller
{
    private $bitacoraModel;
    
    public function __construct()
    {
        $this->bitacoraModel = $this->model('Bitacora');
    }
    
    // Mostrar formulario de login
    public function login()
    {
        // Si ya está logueado, redirigir al inicio
        if (isset($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
        
        $this->view('auth/login');
    }
    
    // Procesar login
    public function autenticar()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Aquí iría la validación contra la BD
            // Por ahora, simulamos un login básico
            
            $_SESSION['usuario_id'] = 1;
            $_SESSION['usuario'] = $username;
            $_SESSION['usuario_nombre'] = 'Admin';
            
            // Registrar en bitácora
            $this->bitacoraModel->registrar(
                $username,
                'Inicio de sesión',
                ['ip' => $_SERVER['REMOTE_ADDR']]
            );
            
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
    }
    
    // Cerrar sesión
    public function logout()
    {
        // Obtener usuario actual
        $usuario = $_SESSION['usuario'] ?? 'Sistema';
        
        // Verificar si es por inactividad
        $expired = isset($_GET['expired']) ? true : false;
        
        // Registrar en bitácora
        if ($expired) {
            $this->bitacoraModel->registrar(
                $usuario,
                'Cierre de sesión por inactividad',
                ['motivo' => 'inactividad', 'tiempo' => '5 minutos']
            );
        } else {
            $this->bitacoraModel->registrar(
                $usuario,
                'Cierre de sesión voluntario'
            );
        }
        
        // Destruir sesión
        session_destroy();
        
        // Redirigir al login
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }
}