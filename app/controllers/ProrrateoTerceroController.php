<?php

class ProrrateoTerceroController extends Controller
{

    private $prorrateoModel;
    private $bitacoraModel;

    public function __construct()
    {
        $this->prorrateoModel = $this->model('ProrrateoTercero');
        $this->bitacoraModel = $this->model('Bitacora');

        if (!isset($_SESSION['usuario'])) {
            $_SESSION['usuario'] = ['username' => 'Admin (Testing)'];
        }
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Metodo no permitido.']);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || !isset($data['id_detalle']) || !isset($data['distribucion'])) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Datos invalidos o incompletos.']);
            return;
        }

        $id_detalle = $data['id_detalle'];
        $distribucion = $data['distribucion'];

        if (!is_array($distribucion) || count($distribucion) === 0) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Debe asignar al menos un tercero.']);
            return;
        }

        $detalle = $this->prorrateoModel->getDetalleAsiento($id_detalle);
        if (!$detalle) {
            http_response_code(404);
            echo json_encode(['exito' => false, 'mensaje' => 'Linea de detalle contable no encontrada.']);
            return;
        }

        $id_asiento = $detalle->id_asiento;
        $monto_linea = $detalle->monto;

        if (!$this->prorrateoModel->validarEstadoAsiento($id_asiento)) {
            http_response_code(403);
            echo json_encode(['exito' => false, 'mensaje' => 'Solo se pueden editar prorrateos en asientos en estado Borrador o Pendiente de Aprobar.']);
            return;
        }

        if (!$this->prorrateoModel->validarSumatoria($monto_linea, $distribucion)) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'La sumatoria del prorrateo no coincide con el monto total de la linea (₡ ' . number_format($monto_linea, 2) . ').']);
            return;
        }

        if (!$this->prorrateoModel->validarTerceros($distribucion)) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Uno o mas terceros seleccionados no existen o estan inactivos.']);
            return;
        }

        if ($this->prorrateoModel->guardarProrrateo($id_detalle, $distribucion)) {
            $usuario = $_SESSION['usuario']['username'] ?? $_SESSION['usuario'];
            $this->bitacoraModel->registrar($usuario, "Prorrateo de Terceros en Detalle L-" . $id_detalle, $distribucion);

            http_response_code(200);
            echo json_encode(['exito' => true, 'mensaje' => 'Prorrateo guardado correctamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['exito' => false, 'mensaje' => 'Error interno al guardar en la base de datos.']);
        }
    }

    public function obtener($id_detalle = 0)
    {
        $id_detalle = (int) $id_detalle;
        if ($id_detalle <= 0) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Id de detalle invalido.']);
            return;
        }

        $distribucion = $this->prorrateoModel->obtenerPorDetalle($id_detalle);
        http_response_code(200);
        echo json_encode($distribucion);
    }
}
