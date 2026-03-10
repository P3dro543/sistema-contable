<?php

class ProrrateoController extends Controller
{

    private $prorrateoModel;
    private $bitacoraModel;

    public function __construct()
    {
        $this->prorrateoModel = $this->model('ProrrateoCentroCosto');
        $this->bitacoraModel = $this->model('Bitacora');

        // Simular usuario para bitácora si no hay sesión
        if (!isset($_SESSION['usuario'])) {
            $_SESSION['usuario'] = 'Admin (Testing)';
        }
    }

    // Endpoint API para recibir el JSon del Frontend
    public function guardar()
    {
        // Solo aceptar POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
            return;
        }

        // Leer el JSON raw del request body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || !isset($data['id_detalle']) || !isset($data['distribucion'])) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Datos inválidos o incompletos.']);
            return;
        }

        $id_detalle = $data['id_detalle'];
        $distribucion = $data['distribucion']; // array de items con id_centro_costo y monto

        // 1. Obtener información de la línea del asiento
        $detalle = $this->prorrateoModel->getDetalleAsiento($id_detalle);
        if (!$detalle) {
            http_response_code(404);
            echo json_encode(['exito' => false, 'mensaje' => 'Línea de detalle contable no encontrada.']);
            return;
        }

        $id_asiento = $detalle->id_asiento;
        $monto_linea = $detalle->monto;

        // 2. Validar que el asiento esté en Borrador o Pendiente de Aprobar
        if (!$this->prorrateoModel->validarEstadoAsiento($id_asiento)) {
            http_response_code(403);
            echo json_encode(['exito' => false, 'mensaje' => 'Sólo se pueden editar prorrateos en asientos en estado Borrador o Pendiente de Aprobar.']);
            return;
        }

        // 3. Validar que la sumatoria sea exactamente igual al monto de la línea
        if (!$this->prorrateoModel->validarSumatoria($monto_linea, $distribucion)) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'La sumatoria del prorrateo no coincide con el monto total de la línea (₡ ' . number_format($monto_linea, 2) . ').']);
            return;
        }

        // 4. Validar que los Centros de Costo existan y estén activos
        if (!$this->prorrateoModel->validarCentrosCosto($distribucion)) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Uno o más centros de costo seleccionados no existen o están inactivos.']);
            return;
        }

        // 5. Guardar el prorrateo
        if ($this->prorrateoModel->guardarProrrateo($id_detalle, $distribucion)) {
            // Registrar en bitácora (JSON del prorrateo guardado)
            $this->bitacoraModel->registrar($_SESSION['usuario'], "Prorrateo de Centros de Costo en Detalle L-" . $id_detalle, $distribucion);

            http_response_code(200);
            echo json_encode(['exito' => true, 'mensaje' => 'Prorrateo guardado correctamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['exito' => false, 'mensaje' => 'Error interno al guardar en la base de datos.']);
        }
    }
}
