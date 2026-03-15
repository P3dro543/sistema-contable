<?php

class TerceroController extends Controller
{

    private $terceroModel;
    private $bitacoraModel;

    public function __construct()
    {
        $this->terceroModel = $this->model('Tercero');
        $this->bitacoraModel = $this->model('Bitacora');

        // Simular usuario para bitácora si no hay sesión
        if (!isset($_SESSION['usuario'])) {
            $_SESSION['usuario'] = 'Admin (Testing)';
        }
    }

   // Listar todos los terceros con paginación
public function index($pagina = 1)
{
    // Validar que la página sea un número y no sea menor a 1
    // Si llega un texto o un 0, se fuerza a que sea 1
    $pagina = (is_numeric($pagina) && (int)$pagina > 0) ? (int)$pagina : 1;

    $limit = 10;
    $offset = ($pagina - 1) * $limit;

    $search = '';
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
    }

    // Ahora $offset nunca será negativo, evitando el error de SQL
    $terceros = $this->terceroModel->getTercerosPaginados($limit, $offset, $search);
    $total = $this->terceroModel->getTotalTerceros($search);
    $totalPaginas = ceil($total / $limit);

    $datos = [
        'terceros' => $terceros,
        'pagina_actual' => $pagina,
        'total_paginas' => $totalPaginas,
        'search' => $search
    ];

    // "Log" en bitácora por la consulta
    if(isset($_SESSION['usuario'])){
        $this->bitacoraModel->registrar($_SESSION['usuario'], "El usuario consulta terceros");
    }

    $this->view('auxiliares/index', $datos);
}

    // Mostrar formulario y procesar creación
    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitizar POST
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $datos = [
                'identificacion' => trim($_POST['identificacion']),
                'nombre' => trim($_POST['nombre']),
                'tipo' => trim($_POST['tipo']),
                'correo' => trim($_POST['correo']),
                'telefono' => trim($_POST['telefono']),
                'estado' => isset($_POST['estado']) ? 1 : 0,
                'error' => ''
            ];

            // Validar si la identificación ya existe
            if ($this->terceroModel->identificacionExiste($datos['identificacion'])) {
                $datos['error'] = "La identificación ya se encuentra registrada para otro tercero.";
                $this->view('auxiliares/form', $datos);
                return;
            }

            // Crear el tercero
            $id = $this->terceroModel->agregarTercero($datos);
            if ($id) {
                // Registro en Bitacora
                $datos['id_tercero'] = $id;
                $this->bitacoraModel->registrar($_SESSION['usuario'], "Creación de Tercero", $datos);

                header('Location: ' . BASE_URL . 'Tercero/index');
            } else {
                die('Algo salió mal');
            }
        } else {
            $datos = [
                'identificacion' => '',
                'nombre' => '',
                'tipo' => '',
                'correo' => '',
                'telefono' => '',
                'estado' => 1,
                'error' => ''
            ];
            $this->view('auxiliares/form', $datos);
        }
    }

    // Mostrar formulario y procesar edición
    public function editar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitizar POST
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $datos = [
                'id_tercero' => $id,
                'identificacion' => trim($_POST['identificacion']),
                'nombre' => trim($_POST['nombre']),
                'tipo' => trim($_POST['tipo']),
                'correo' => trim($_POST['correo']),
                'telefono' => trim($_POST['telefono']),
                'estado' => isset($_POST['estado']) ? 1 : 0,
                'error' => ''
            ];

            // Validar si la identificación ya existe para OTRO tercero
            if ($this->terceroModel->identificacionExiste($datos['identificacion'], $id)) {
                $datos['error'] = "La identificación ya está registrada en otro tercero.";
                $this->view('auxiliares/form', $datos);
                return;
            }

            // Obtener el registro viejo para la bitacora
            $registro_anterior = $this->terceroModel->getTerceroById($id);

            // Actualizar
            if ($this->terceroModel->actualizarTercero($datos)) {
                // Bitacora
                $info_bitacora = [
                    'Anterior' => $registro_anterior,
                    'Nuevo' => $datos
                ];
                $this->bitacoraModel->registrar($_SESSION['usuario'], "Actualización de Tercero", $info_bitacora);

                header('Location: ' . BASE_URL . 'Tercero/index');
            } else {
                die('Algo salió mal');
            }
        } else {
            // Obtener información del tercero
            $tercero = $this->terceroModel->getTerceroById($id);

            $datos = [
                'id_tercero' => $tercero->id_tercero,
                'identificacion' => $tercero->identificacion,
                'nombre' => $tercero->nombre,
                'tipo' => $tercero->tipo,
                'correo' => $tercero->correo,
                'telefono' => $tercero->telefono,
                'estado' => $tercero->estado,
                'error' => ''
            ];
            $this->view('auxiliares/form', $datos);
        }
    }

    // Procesar eliminación
    public function eliminar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Obtener info para la bitácora
            $tercero = $this->terceroModel->getTerceroById($id);

            $resultado = $this->terceroModel->eliminarTercero($id);

            if ($resultado === true) {
                // Bitacora
                $this->bitacoraModel->registrar($_SESSION['usuario'], "Eliminación de Tercero", $tercero);
                header('Location: ' . BASE_URL . 'Tercero/index?msg=eliminado');
            } else if ($resultado === "relacionado") {
                header('Location: ' . BASE_URL . 'Tercero/index?error=No+se+puede+eliminar+un+registro+con+datos+relacionados.');
            } else {
                die('Error al eliminar');
            }
        } else {
            header('Location: ' . BASE_URL . 'Tercero/index');
        }
    }
}
