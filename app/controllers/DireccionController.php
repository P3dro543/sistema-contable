<?php
/**
 * Controlador: DireccionController
 * Maneja el CRUD de direcciones asociadas a un tercero (AUX11).
 */

require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/DireccionTercero.php';
require_once __DIR__ . '/../../app/models/Bitacora.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

class DireccionController {

    private DireccionTercero $model;
    private Bitacora         $bitacora;
    private string           $usuarioActivo;

    public function __construct() {
        AuthController::verificarSesion();

        $db = Database::getConnection();
        $this->model         = new DireccionTercero($db);
        $this->bitacora      = new Bitacora($db);
        $this->usuarioActivo = $_SESSION['usuario']['username'];
    }

    // ------------------------------------------------------------------ //
    //  Listado de direcciones de un tercero
    // ------------------------------------------------------------------ //
    public function index(): void {
        $idTercero = (int) ($_GET['id_tercero'] ?? 0);
        if ($idTercero === 0) {
            header('Location: index.php?ruta=terceros');
            exit;
        }

        $pagina    = max(1, (int) ($_GET['pagina'] ?? 1));
        $porPagina = 10;
        $total     = $this->model->contar($idTercero);
        $totalPags = (int) ceil($total / $porPagina);
        $registros = $this->model->listar($idTercero, $pagina, $porPagina);

        $this->bitacora->registrar(
            $this->usuarioActivo,
            'CONSULTA',
            "El usuario consulta direcciones del tercero #{$idTercero}"
        );

        $mensaje = $_SESSION['flash_msg'] ?? null;
        unset($_SESSION['flash_msg']);

        require_once __DIR__ . '/../../views/auxiliares/direcciones_index.php';
    }

    // ------------------------------------------------------------------ //
    //  Formulario de creación
    // ------------------------------------------------------------------ //
    public function crear(): void {
        $idTercero = (int) ($_GET['id_tercero'] ?? 0);
        if ($idTercero === 0) {
            header('Location: index.php?ruta=terceros');
            exit;
        }

        $errores = $_SESSION['form_errores'] ?? [];
        $datos   = $_SESSION['form_datos']   ?? [];
        unset($_SESSION['form_errores'], $_SESSION['form_datos']);

        $accion = 'crear';
        require_once __DIR__ . '/../../views/auxiliares/direcciones_form.php';
    }

    // ------------------------------------------------------------------ //
    //  Guardar nueva dirección
    // ------------------------------------------------------------------ //
    public function guardar(): void {
        $idTercero = (int) ($_POST['id_tercero'] ?? 0);

        $datos = [
            'id_tercero'       => $idTercero,
            'alias'            => trim($_POST['alias']            ?? ''),
            'provincia'        => trim($_POST['provincia']        ?? ''),
            'canton'           => trim($_POST['canton']           ?? ''),
            'distrito'         => trim($_POST['distrito']         ?? ''),
            'direccion_exacta' => trim($_POST['direccion_exacta'] ?? ''),
            'estado'           => isset($_POST['estado'])    ? 1 : 0,
            'principal'        => isset($_POST['principal']) ? 1 : 0,
        ];

        $errores = $this->validar($datos);

        if (!empty($errores)) {
            $_SESSION['form_errores'] = $errores;
            $_SESSION['form_datos']   = $datos;
            header("Location: index.php?ruta=direcciones_crear&id_tercero={$idTercero}");
            exit;
        }

        try {
            $nuevoId = $this->model->crear($datos);
            $this->bitacora->registrar($this->usuarioActivo, 'CREAR_DIRECCION', $datos + ['id_nuevo' => $nuevoId]);
            $_SESSION['flash_msg'] = ['tipo' => 'success', 'texto' => 'Dirección creada correctamente.'];
        } catch (Exception $e) {
            $this->bitacora->registrar($this->usuarioActivo, 'ERROR_CREAR_DIRECCION', ['error' => $e->getMessage()]);
            $_SESSION['flash_msg'] = ['tipo' => 'danger', 'texto' => 'Error: ' . $e->getMessage()];
        }       

        header("Location: index.php?ruta=direcciones&id_tercero={$idTercero}");
        exit;
    }

    // ------------------------------------------------------------------ //
    //  Formulario de edición
    // ------------------------------------------------------------------ //
    public function editar(): void {
        $id = (int) ($_GET['id'] ?? 0);
        $registro = $this->model->obtener($id);

        if (!$registro) {
            $_SESSION['flash_msg'] = ['tipo' => 'warning', 'texto' => 'Dirección no encontrada.'];
            header('Location: index.php?ruta=terceros');
            exit;
        }

        $errores = $_SESSION['form_errores'] ?? [];
        $datos   = $_SESSION['form_datos']   ?? $registro;
        unset($_SESSION['form_errores'], $_SESSION['form_datos']);

        $idTercero = (int) $registro['id_tercero'];
        $accion    = 'editar';
        require_once __DIR__ . '/../../views/auxiliares/direcciones_form.php';
    }

    // ------------------------------------------------------------------ //
    //  Actualizar dirección existente
    // ------------------------------------------------------------------ //
    public function actualizar(): void {
        $id        = (int) ($_POST['id_direccion'] ?? 0);
        $idTercero = (int) ($_POST['id_tercero']   ?? 0);

        $anterior = $this->model->obtener($id);
        if (!$anterior) {
            $_SESSION['flash_msg'] = ['tipo' => 'warning', 'texto' => 'Dirección no encontrada.'];
            header("Location: index.php?ruta=direcciones&id_tercero={$idTercero}");
            exit;
        }

        $datos = [
            'alias'            => trim($_POST['alias']            ?? ''),
            'provincia'        => trim($_POST['provincia']        ?? ''),
            'canton'           => trim($_POST['canton']           ?? ''),
            'distrito'         => trim($_POST['distrito']         ?? ''),
            'direccion_exacta' => trim($_POST['direccion_exacta'] ?? ''),
            'estado'           => isset($_POST['estado'])    ? 1 : 0,
            'principal'        => isset($_POST['principal']) ? 1 : 0,
        ];

        $errores = $this->validar($datos);

        if (!empty($errores)) {
            $_SESSION['form_errores'] = $errores;
            $_SESSION['form_datos']   = array_merge($anterior, $datos);
            header("Location: index.php?ruta=direcciones_editar&id={$id}");
            exit;
        }

        try {
            $this->model->actualizar($id, $datos);
            $this->bitacora->registrar($this->usuarioActivo, 'ACTUALIZAR_DIRECCION', [
                'anterior' => $anterior,
                'nuevo'    => $datos + ['id_direccion' => $id],
            ]);
            $_SESSION['flash_msg'] = ['tipo' => 'success', 'texto' => 'Dirección actualizada correctamente.'];
        } catch (Exception $e) {
            $this->bitacora->registrar($this->usuarioActivo, 'ERROR_ACTUALIZAR_DIRECCION', ['error' => $e->getMessage()]);
            $_SESSION['flash_msg'] = ['tipo' => 'danger', 'texto' => 'Error al actualizar la dirección.'];
        }

        header("Location: index.php?ruta=direcciones&id_tercero={$idTercero}");
        exit;
    }

    // ------------------------------------------------------------------ //
    //  Eliminar dirección
    // ------------------------------------------------------------------ //
    public function eliminar(): void {
        $id        = (int) ($_POST['id_direccion'] ?? 0);
        $idTercero = (int) ($_POST['id_tercero']   ?? 0);

        $registro = $this->model->obtener($id);
        if (!$registro) {
            $_SESSION['flash_msg'] = ['tipo' => 'warning', 'texto' => 'Dirección no encontrada.'];
            header("Location: index.php?ruta=direcciones&id_tercero={$idTercero}");
            exit;
        }

        try {
            $eliminado = $this->model->eliminar($id);
            if ($eliminado) {
                $this->bitacora->registrar($this->usuarioActivo, 'ELIMINAR_DIRECCION', $registro);
                $_SESSION['flash_msg'] = ['tipo' => 'success', 'texto' => 'Dirección eliminada correctamente.'];
            } else {
                $_SESSION['flash_msg'] = ['tipo' => 'danger', 'texto' => 'No se pudo eliminar la dirección.'];
            }
        } catch (PDOException $e) {
            $this->bitacora->registrar($this->usuarioActivo, 'ERROR_ELIMINAR_DIRECCION', ['error' => $e->getMessage()]);
            $_SESSION['flash_msg'] = ['tipo' => 'danger', 'texto' => 'No se puede eliminar un registro con datos relacionados.'];
        }

        header("Location: index.php?ruta=direcciones&id_tercero={$idTercero}");
        exit;
    }

    // ------------------------------------------------------------------ //
    //  Validaciones
    // ------------------------------------------------------------------ //
    private function validar(array $datos): array {
        $errores = [];

        if (empty($datos['alias'])) {
            $errores['alias'] = 'El alias es requerido.';
        } elseif (strlen($datos['alias']) > 100) {
            $errores['alias'] = 'El alias no debe superar 100 caracteres.';
        }

        if (empty($datos['direccion_exacta'])) {
            $errores['direccion_exacta'] = 'La dirección exacta es requerida.';
        } elseif (strlen($datos['direccion_exacta']) > 255) {
            $errores['direccion_exacta'] = 'La dirección exacta no debe superar 255 caracteres.';
        }

        return $errores;
    }
}