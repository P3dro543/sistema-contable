<?php

class ProrrateoCentroCosto
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Validar el estado del asiento
    public function validarEstadoAsiento($id_asiento)
    {
        // En Buscar el asiento y verificar su estado (Borrador = 3, Pendiente = 4, etc. - asumiendo IDs de estados en BD)
        // Para este proyecto asumo q se unen tablas asiento y estado
        $sql = "SELECT ea.nombre FROM asientos a 
                JOIN estados_asiento ea ON a.id_estado = ea.id_estado 
                WHERE a.id_asiento = :id_asiento";
        $this->db->query($sql);
        $this->db->bind(':id_asiento', $id_asiento);

        $estado = $this->db->single();
        if ($estado) {
            $nombreEstado = strtolower(trim($estado->nombre));
            if ($nombreEstado == 'borrador' || $nombreEstado == 'pendiente de aprobacion' || $nombreEstado == 'pendiente de aprobar') {
                return true;
            }
        }
        return false;
    }

    // Obtener la información de una línea de detalle del asiento
    public function getDetalleAsiento($id_detalle)
    {
        $this->db->query("SELECT * FROM asiento_detalle WHERE id_detalle = :id_detalle");
        $this->db->bind(':id_detalle', $id_detalle);
        return $this->db->single();
    }

    // Validar suma del prorrateo
    public function validarSumatoria($monto_linea, $distribucion)
    {
        $suma = 0;
        foreach ($distribucion as $item) {
            $suma += (float) $item['monto'];
        }
        // Validacion exacta
        return round($monto_linea, 2) === round($suma, 2);
    }

    // Validar si centros de costo existen y están activos
    public function validarCentrosCosto($distribucion)
    {
        foreach ($distribucion as $item) {
            $id_cc = $item['id_centro_costo'];
            $this->db->query("SELECT estado FROM centros_costo WHERE id_centro_costo = :id_cc");
            $this->db->bind(':id_cc', $id_cc);
            $cc = $this->db->single();

            if (!$cc || $cc->estado != 1) { // 1 es activo
                return false;
            }
        }
        return true;
    }

    // Guardar prorrateo de una linea
    public function guardarProrrateo($id_detalle, $distribucion)
    {
        try {
            // Empezar transaccion PDO
            $this->db->query("START TRANSACTION");
            $this->db->execute();

            // 1. Borrar prorrateo anterior si existiera
            $this->db->query("DELETE FROM prorrateo_centro_costo WHERE id_detalle = :id_detalle");
            $this->db->bind(':id_detalle', $id_detalle);
            $this->db->execute();

            // 2. Insertar nueva distribucion
            $this->db->query("INSERT INTO prorrateo_centro_costo (id_detalle, id_centro_costo, monto) 
                              VALUES (:id_detalle, :id_centro_costo, :monto)");

            foreach ($distribucion as $item) {
                $this->db->bind(':id_detalle', $id_detalle);
                $this->db->bind(':id_centro_costo', $item['id_centro_costo']);
                $this->db->bind(':monto', $item['monto']);
                $this->db->execute();
            }

            // Confirmar transaccion
            $this->db->query("COMMIT");
            $this->db->execute();
            return true;

        } catch (Exception $e) {
            // Revertir transaccion en caso de fallo
            $this->db->query("ROLLBACK");
            $this->db->execute();
            return false;
        }
    }
}
