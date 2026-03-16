<?php

class ProrrateoTercero
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Validar el estado del asiento
    public function validarEstadoAsiento($id_asiento)
    {
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
        return round($monto_linea, 2) === round($suma, 2);
    }

    // Validar si terceros existen y están activos
    public function validarTerceros($distribucion)
    {
        foreach ($distribucion as $item) {
            $id_tercero = $item['id_tercero'];
            $this->db->query("SELECT estado FROM terceros WHERE id_tercero = :id_tercero");
            $this->db->bind(':id_tercero', $id_tercero);
            $tercero = $this->db->single();

            if (!$tercero || (int) $tercero->estado !== 1) {
                return false;
            }
        }
        return true;
    }

    // Guardar prorrateo de una linea
    public function guardarProrrateo($id_detalle, $distribucion)
    {
        try {
            $this->db->query("START TRANSACTION");
            $this->db->execute();

            $this->db->query("DELETE FROM prorrateo_tercero WHERE id_detalle = :id_detalle");
            $this->db->bind(':id_detalle', $id_detalle);
            $this->db->execute();

            $this->db->query("INSERT INTO prorrateo_tercero (id_detalle, id_tercero, monto) 
                              VALUES (:id_detalle, :id_tercero, :monto)");

            foreach ($distribucion as $item) {
                $this->db->bind(':id_detalle', $id_detalle);
                $this->db->bind(':id_tercero', $item['id_tercero']);
                $this->db->bind(':monto', $item['monto']);
                $this->db->execute();
            }

            $this->db->query("COMMIT");
            $this->db->execute();
            return true;

        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            $this->db->execute();
            return false;
        }
    }

    public function obtenerPorDetalle($id_detalle)
    {
        $this->db->query("SELECT id_tercero, monto FROM prorrateo_tercero WHERE id_detalle = :id_detalle");
        $this->db->bind(':id_detalle', $id_detalle);
        return $this->db->resultSet();
    }
}
