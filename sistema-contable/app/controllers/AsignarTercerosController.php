<?php

class AsignarTercerosController extends Controller
{
    private $asientoModel;
    private $periodoModel;
    private $terceroModel;
    private $bitacoraModel;

    public function __construct()
    {
        $this->asientoModel = $this->model('Asiento');
        $this->periodoModel = $this->model('Periodo');
        $this->terceroModel = $this->model('Tercero');
        $this->bitacoraModel = $this->model('Bitacora');

        if (!isset($_SESSION['usuario'])) {
            $_SESSION['usuario'] = 'Admin (Testing)';
        }
    }

    private function estadoPermiteAsignacion($estadoNombre)
    {
        $nombre = strtolower(trim((string) $estadoNombre));
        return ($nombre === 'borrador' || $nombre === 'pendiente de aprobacion' || $nombre === 'pendiente de aprobar');
    }

    public function index($pagina = 1)
    {
        $limit = 10;
        $offset = ($pagina - 1) * $limit;

        $periodos = $this->periodoModel->getPeriodos();
        $periodoAbierto = $this->periodoModel->getPeriodoAbiertoActualId();

        $periodoSeleccionado = null;
        if (isset($_GET['id_periodo']) && $_GET['id_periodo'] !== '') {
            $periodoSeleccionado = (int) $_GET['id_periodo'];
        } else if ($periodoAbierto !== null) {
            $periodoSeleccionado = (int) $periodoAbierto;
        } else if (!empty($periodos)) {
            $periodoSeleccionado = (int) $periodos[0]->id_periodo;
        }

        $asientos = $this->asientoModel->getAsientosPaginados($limit, $offset, $periodoSeleccionado);
        $total = $this->asientoModel->getTotalAsientos($periodoSeleccionado);
        $totalPaginas = ceil($total / $limit);

        $detallesPorAsiento = [];
        foreach ($asientos as $a) {
            $detallesPorAsiento[$a->id_asiento] = $this->asientoModel->getDetallesPorAsiento($a->id_asiento);
        }

        $tercerosActivos = $this->terceroModel->getTercerosActivos();

        $datos = [
            'periodos' => $periodos,
            'periodo_abierto_id' => $periodoAbierto,
            'periodo_seleccionado_id' => $periodoSeleccionado,
            'asientos' => $asientos,
            'detalles_por_asiento' => $detallesPorAsiento,
            'terceros_activos' => $tercerosActivos,
            'pagina_actual' => $pagina,
            'total_paginas' => $totalPaginas,
        ];

        $this->bitacoraModel->registrar($_SESSION['usuario'], "El usuario consulta asientos para asignar terceros", [
            'id_periodo' => $periodoSeleccionado
        ]);

        $this->view('asientos/asignar_terceros', $datos);
    }
}

