<?php

class ProrrateoController extends Controller
{

    private $prorrateoModel;
    private $bitacoraModel;

    public function __construct()
    {
        $this->prorrateoModel = $this->model('ProrrateoCentroCosto');
        $this->bitacoraModel = $this->model('Bitacora');

        // Simular usuario para bitÃ¡cora si no hay sesiÃ³n
        if (!isset($_SESSION['usuario'])) {
            $_SESSION['usuario'] = 'Admin (Testing)';
        }
    }

    // Endpoint API para recibir el JSon del Frontend
    public function guardar()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Solo aceptar POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Metodo no permitido.']);
            return;
        }

        // Leer el JSON raw del request body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || !isset($data['id_detalle']) || !isset($data['distribucion'])) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Datos invalidos o incompletos.']);
            return;
        }

        $id_detalle = $data['id_detalle'];
        $distribucion = $data['distribucion']; // array de items con id_centro_costo y monto

        // 1. Obtener informaciÃ³n de la lÃ­nea del asiento
        $detalle = $this->prorrateoModel->getDetalleAsiento($id_detalle);
        if (!$detalle) {
            http_response_code(404);
            echo json_encode(['exito' => false, 'mensaje' => 'Linea de detalle contable no encontrada.']);
            return;
        }

        $id_asiento = $detalle->id_asiento;
        $monto_linea = $detalle->monto;

        // 2. Validar que el asiento estÃ© en Borrador o Pendiente de Aprobar
        if (!$this->prorrateoModel->validarEstadoAsiento($id_asiento)) {
            http_response_code(403);
            echo json_encode(['exito' => false, 'mensaje' => 'SÃ³lo se pueden editar prorrateos en asientos en estado Borrador o Pendiente de Aprobar.']);
            return;
        }

        // 3. Validar que la sumatoria sea exactamente igual al monto de la lÃ­nea
        if (!$this->prorrateoModel->validarSumatoria($monto_linea, $distribucion)) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'La sumatoria del prorrateo no coincide con el monto total de la linea (' . number_format($monto_linea, 2) . ').']);
            return;
        }

        // 4. Validar que los Centros de Costo existan y estÃ©n activos
        if (!$this->prorrateoModel->validarCentrosCosto($distribucion)) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Uno o mÃ¡s centros de costo seleccionados no existen o estÃ¡n inactivos.']);
            return;
        }

        // 5. Guardar el prorrateo
        if ($this->prorrateoModel->guardarProrrateo($id_detalle, $distribucion)) {
            // Registrar en bitÃ¡cora (JSON del prorrateo guardado)
            $this->bitacoraModel->registrar($_SESSION['usuario'], "Prorrateo de Centros de Costo en Detalle L-" . $id_detalle, $distribucion);

            http_response_code(200);
            echo json_encode(['exito' => true, 'mensaje' => 'Prorrateo guardado correctamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['exito' => false, 'mensaje' => 'Error interno al guardar en la base de datos.']);
        }
    }
}
