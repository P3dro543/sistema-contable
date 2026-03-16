<?php
/**
 * Controlador: CentroCostoController
 * CRUD de centros de costo (AUX6).
 */

require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/CentroCosto.php';
require_once __DIR__ . '/../../app/models/Bitacora.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

class CentroCostoController {

    private CentroCosto $model;
    private Bitacora $bitacora;
    private string $usuarioActivo;

    public function __construct() {
        AuthController::verificarSesion();

        $db = Database::getConnection();
        $this->model = new CentroCosto($db);
        $this->bitacora = new Bitacora($db);
        $this->usuarioActivo = $_SESSION['usuario']['username'] ?? 'desconocido';
    }

    // ------------------------------------------------------------------ //
    //  Listado paginado
    // ------------------------------------------------------------------ //
    public function index(): void {
        $pagina    = max(1, (int) ($_GET['pagina'] ?? 1));
        $porPagina = 10;
        $total     = $this->model->contar();
        $totalPags = (int) ceil($total / $porPagina);
        $registros = $this->model->listar($pagina, $porPagina);

        $this->bitacora->registrar($this->usuarioActivo, 'CONSULTA_CENTROS_COSTO', [
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
        ]);

        $mensaje = $_SESSION['flash_msg'] ?? null;
        unset($_SESSION['flash_msg']);

        require_once __DIR__ . '/../../views/centros_costo/index.php';
    }

    // ------------------------------------------------------------------ //
    //  Formulario de creación
    // ------------------------------------------------------------------ //
    public function crear(): void {
        $errores = $_SESSION['form_errores'] ?? [];
        $datos   = $_SESSION['form_datos']   ?? [];
        unset($_SESSION['form_errores'], $_SESSION['form_datos']);

        $accion = 'crear';
        require_once __DIR__ . '/../../views/centros_costo/form.php';
    }

    // ------------------------------------------------------------------ //
    //  Guardar nuevo centro de costo
    // ------------------------------------------------------------------ //
    public function guardar(): void {
        $datos = [
            'codigo'      => trim($_POST['codigo'] ?? ''),
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estado'      => isset($_POST['estado']) ? 1 : 0,
        ];

        $errores = $this->validar($datos);
        if (!empty($errores)) {
            $_SESSION['form_errores'] = $errores;
            $_SESSION['form_datos']   = $datos;
            header('Location: index.php?ruta=centros_costo_crear');
            exit;
        }

        try {
            $nuevoId = $this->model->crear($datos);
            $this->bitacora->registrar($this->usuarioActivo, 'CREAR_CENTRO_COSTO', $datos + ['id_centro_costo' => $nuevoId]);
            $_SESSION['flash_msg'] = ['tipo' => 'success', 'texto' => 'Centro de costo creado correctamente.'];
        } catch (Exception $e) {
            $this->bitacora->registrar($this->usuarioActivo, 'ERROR_CREAR_CENTRO_COSTO', ['error' => $e->getMessage()]);
            $_SESSION['flash_msg'] = ['tipo' => 'danger', 'texto' => 'Error al crear el centro de costo.'];
        }

        header('Location: index.php?ruta=centros_costo');
        exit;
    }

    // ------------------------------------------------------------------ //
    //  Formulario de edición
    // ------------------------------------------------------------------ //
    public function editar(): void {
        $id = (int) ($_GET['id'] ?? 0);
        $registro = $this->model->obtener($id);

        if (!$registro) {
            $_SESSION['flash_msg'] = ['tipo' => 'warning', 'texto' => 'Centro de costo no encontrado.'];
            header('Location: index.php?ruta=centros_costo');
            exit;
        }

        $errores = $_SESSION['form_errores'] ?? [];
        $datos   = $_SESSION['form_datos']   ?? $registro;
        unset($_SESSION['form_errores'], $_SESSION['form_datos']);

        $accion = 'editar';
        require_once __DIR__ . '/../../views/centros_costo/form.php';
    }

    // ------------------------------------------------------------------ //
    //  Actualizar centro de costo
    // ------------------------------------------------------------------ //
    public function actualizar(): void {
        $id = (int) ($_POST['id_centro_costo'] ?? 0);
        $anterior = $this->model->obtener($id);

        if (!$anterior) {
            $_SESSION['flash_msg'] = ['tipo' => 'warning', 'texto' => 'Centro de costo no encontrado.'];
            header('Location: index.php?ruta=centros_costo');
            exit;
        }

        $datos = [
            'codigo'      => trim($_POST['codigo'] ?? ''),
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'estado'      => isset($_POST['estado']) ? 1 : 0,
        ];

        $errores = $this->validar($datos, $id);
        if (!empty($errores)) {
            $_SESSION['form_errores'] = $errores;
            $_SESSION['form_datos']   = $datos + ['id_centro_costo' => $id];
            header('Location: index.php?ruta=centros_costo_editar&id=' . $id);
            exit;
        }

        try {
            $this->model->actualizar($id, $datos);
            $this->bitacora->registrar($this->usuarioActivo, 'ACTUALIZAR_CENTRO_COSTO', [
                'anterior' => $anterior,
                'nuevo'    => $datos + ['id_centro_costo' => $id],
            ]);
            $_SESSION['flash_msg'] = ['tipo' => 'success', 'texto' => 'Centro de costo actualizado correctamente.'];
        } catch (Exception $e) {
            $this->bitacora->registrar($this->usuarioActivo, 'ERROR_ACTUALIZAR_CENTRO_COSTO', ['error' => $e->getMessage()]);
            $_SESSION['flash_msg'] = ['tipo' => 'danger', 'texto' => 'Error al actualizar el centro de costo.'];
        }

        header('Location: index.php?ruta=centros_costo');
        exit;
    }

    // ------------------------------------------------------------------ //
    //  Eliminar centro de costo
    // ------------------------------------------------------------------ //
    public function eliminar(): void {
        $id = (int) ($_POST['id_registro'] ?? 0);
        $registro = $this->model->obtener($id);

        if (!$registro) {
            $_SESSION['flash_msg'] = ['tipo' => 'warning', 'texto' => 'Centro de costo no encontrado.'];
            header('Location: index.php?ruta=centros_costo');
            exit;
        }

        if ($this->model->tieneRelaciones($id)) {
            $_SESSION['flash_msg'] = ['tipo' => 'danger', 'texto' => 'No se puede eliminar un registro con datos relacionados'];
            header('Location: index.php?ruta=centros_costo');
            exit;
        }

        try {
            $this->model->eliminar($id);
            $this->bitacora->registrar($this->usuarioActivo, 'ELIMINAR_CENTRO_COSTO', $registro);
            $_SESSION['flash_msg'] = ['tipo' => 'success', 'texto' => 'Centro de costo eliminado correctamente.'];
        } catch (Exception $e) {
            $this->bitacora->registrar($this->usuarioActivo, 'ERROR_ELIMINAR_CENTRO_COSTO', ['error' => $e->getMessage()]);
            $_SESSION['flash_msg'] = ['tipo' => 'danger', 'texto' => 'No se pudo eliminar el centro de costo.'];
        }

        header('Location: index.php?ruta=centros_costo');
        exit;
    }

    // ------------------------------------------------------------------ //
    //  Validaciones
    // ------------------------------------------------------------------ //
    private function validar(array $datos, int $id = 0): array {
        $errores = [];

        if (empty($datos['codigo'])) {
            $errores['codigo'] = 'El codigo es requerido.';
        } elseif (strlen($datos['codigo']) > 20) {
            $errores['codigo'] = 'El codigo no debe superar 20 caracteres.';
        } elseif ($this->model->codigoExiste($datos['codigo'], $id)) {
            $errores['codigo'] = 'El codigo ya existe.';
        }

        if (empty($datos['nombre'])) {
            $errores['nombre'] = 'El nombre es requerido.';
        } elseif (strlen($datos['nombre']) > 120) {
            $errores['nombre'] = 'El nombre no debe superar 120 caracteres.';
        }

        if (!empty($datos['descripcion']) && strlen($datos['descripcion']) > 255) {
            $errores['descripcion'] = 'La descripcion no debe superar 255 caracteres.';
        }

        return $errores;
    }
}
