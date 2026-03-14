<?php

class CentroCostoController extends Controller
{
    private $centroCostoModel;
    private $bitacoraModel;

    public function __construct()
    {
        $this->centroCostoModel = $this->model('CentroCosto');
        $this->bitacoraModel = $this->model('Bitacora');

        if (!isset($_SESSION['usuario'])) {
            $_SESSION['usuario'] = 'Admin (Testing)';
        }
    }

    public function index($pagina = 1)
    {
        $limit = 10;
        $offset = ($pagina - 1) * $limit;

        $search = '';
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        }

        $centros = $this->centroCostoModel->getCentrosCostoPaginados($limit, $offset, $search);
        $total = $this->centroCostoModel->getTotalCentrosCosto($search);
        $totalPaginas = ceil($total / $limit);

        $datos = [
            'centros' => $centros,
            'pagina_actual' => $pagina,
            'total_paginas' => $totalPaginas,
            'search' => $search
        ];

        $this->bitacoraModel->registrar($_SESSION['usuario'], "El usuario consulta centros de costo");
        $this->view('centros_costo/index', $datos);
    }

    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $datos = [
                'codigo' => trim($_POST['codigo']),
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'estado' => isset($_POST['estado']) ? 1 : 0,
                'error' => ''
            ];

            if ($this->centroCostoModel->codigoExiste($datos['codigo'])) {
                $datos['error'] = "El código ya se encuentra registrado para otro Centro de Costo.";
                $this->view('centros_costo/form', $datos);
                return;
            }

            $id = $this->centroCostoModel->agregarCentroCosto($datos);
            if ($id) {
                $datos['id_centro_costo'] = $id;
                $this->bitacoraModel->registrar($_SESSION['usuario'], "Creación de Centro de Costo", $datos);
                header('Location: ' . BASE_URL . 'CentroCosto/index');
            } else {
                die('Algo salió mal');
            }
        } else {
            $datos = [
                'codigo' => '',
                'nombre' => '',
                'descripcion' => '',
                'estado' => 1,
                'error' => ''
            ];
            $this->view('centros_costo/form', $datos);
        }
    }

    public function editar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $datos = [
                'id_centro_costo' => $id,
                'codigo' => trim($_POST['codigo']),
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'estado' => isset($_POST['estado']) ? 1 : 0,
                'error' => ''
            ];

            if ($this->centroCostoModel->codigoExiste($datos['codigo'], $id)) {
                $datos['error'] = "El código ya está registrado en otro Centro de Costo.";
                $this->view('centros_costo/form', $datos);
                return;
            }

            $registro_anterior = $this->centroCostoModel->getCentroCostoById($id);

            if ($this->centroCostoModel->actualizarCentroCosto($datos)) {
                $info_bitacora = [
                    'Anterior' => $registro_anterior,
                    'Nuevo' => $datos
                ];
                $this->bitacoraModel->registrar($_SESSION['usuario'], "Actualización de Centro de Costo", $info_bitacora);
                header('Location: ' . BASE_URL . 'CentroCosto/index');
            } else {
                die('Algo salió mal');
            }
        } else {
            $centro = $this->centroCostoModel->getCentroCostoById($id);
            if (!$centro) {
                header('Location: ' . BASE_URL . 'CentroCosto/index?error=Registro+no+encontrado.');
                return;
            }

            $datos = [
                'id_centro_costo' => $centro->id_centro_costo,
                'codigo' => $centro->codigo ?? '',
                'nombre' => $centro->nombre ?? '',
                'descripcion' => $centro->descripcion ?? '',
                'estado' => $centro->estado ?? 1,
                'error' => ''
            ];
            $this->view('centros_costo/form', $datos);
        }
    }

    public function eliminar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $centro = $this->centroCostoModel->getCentroCostoById($id);

            $resultado = $this->centroCostoModel->eliminarCentroCosto($id);

            if ($resultado === true) {
                $this->bitacoraModel->registrar($_SESSION['usuario'], "Eliminación de Centro de Costo", $centro);
                header('Location: ' . BASE_URL . 'CentroCosto/index?msg=eliminado');
            } else if ($resultado === "relacionado") {
                header('Location: ' . BASE_URL . 'CentroCosto/index?error=No+se+puede+eliminar+un+registro+con+datos+relacionados.');
            } else {
                die('Error al eliminar');
            }
        } else {
            header('Location: ' . BASE_URL . 'CentroCosto/index');
        }
    }
}

