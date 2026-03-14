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
            $_SESSION['usuario'] = 'Admin (Testing)';
        }
    }

    public function guardar()
    {
        header('Content-Type: application/json; charset=utf-8');

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

        if (!is_numeric($id_detalle) || (int) $id_detalle <= 0) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'El id_detalle es inválido.']);
            return;
        }
        $id_detalle = (int) $id_detalle;

        if (!is_array($distribucion) || count($distribucion) < 1) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Debe asignar al menos un tercero.']);
            return;
        }

        foreach ($distribucion as $i => $item) {
            if (!is_array($item) || !isset($item['id_tercero']) || !isset($item['monto'])) {
                http_response_code(400);
                echo json_encode(['exito' => false, 'mensaje' => 'La distribución contiene elementos inválidos.']);
                return;
            }
            if (!is_numeric($item['id_tercero']) || (int) $item['id_tercero'] <= 0) {
                http_response_code(400);
                echo json_encode(['exito' => false, 'mensaje' => 'Debe seleccionar un tercero válido en cada línea.']);
                return;
            }
            if (!is_numeric($item['monto']) || (float) $item['monto'] < 0) {
                http_response_code(400);
                echo json_encode(['exito' => false, 'mensaje' => 'El monto de cada línea debe ser un número válido mayor o igual a 0.']);
                return;
            }
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
            echo json_encode(['exito' => false, 'mensaje' => 'SÃ³lo se pueden asignar terceros en asientos en estado Borrador o Pendiente de Aprobar.']);
            return;
        }

        if (!$this->prorrateoModel->validarSumatoria($monto_linea, $distribucion)) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'La sumatoria del prorrateo no coincide con el monto total de la linea (' . number_format($monto_linea, 2) . ').']);
            return;
        }

        if (!$this->prorrateoModel->validarTerceros($distribucion)) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Uno o mÃ¡s terceros seleccionados no existen o estÃ¡n inactivos.']);
            return;
        }

        $resultado = $this->prorrateoModel->guardarProrrateo($id_detalle, $distribucion);
        if ($resultado === "tabla_no_existe") {
            http_response_code(500);
            echo json_encode(['exito' => false, 'mensaje' => 'No existe la tabla prorrateo_tercero en la base de datos.']);
            return;
        }

        if ($resultado === true) {
            $this->bitacoraModel->registrar($_SESSION['usuario'], "Prorrateo de Terceros en Detalle L-" . $id_detalle, $distribucion);
            http_response_code(200);
            echo json_encode(['exito' => true, 'mensaje' => 'Asignacion guardada correctamente.']);
            return;
        }

        http_response_code(500);
        echo json_encode(['exito' => false, 'mensaje' => 'Error interno al guardar en la base de datos.']);
    }
}
