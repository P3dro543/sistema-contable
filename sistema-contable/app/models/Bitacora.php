<?php

class Bitacora
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Registrar acción en la bitácora
    public function registrar($usuario, $accion, $detalle_array = null)
    {
        $detalleJson = null;
        if ($detalle_array !== null) {
            $detalleJson = json_encode($detalle_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // Guardar el JSON en la columna detalle_json (requisito de bitácoras).
        $this->db->query('INSERT INTO bitacora (fecha, usuario, accion, detalle_json) VALUES (NOW(), :usuario, :accion, :detalle_json)');

        $this->db->bind(':usuario', $usuario);
        $this->db->bind(':accion', $accion);
        $this->db->bind(':detalle_json', $detalleJson);

        return $this->db->execute();
    }
}
