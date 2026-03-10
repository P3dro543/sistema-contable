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
        $descripcion = $accion;
        if ($detalle_array !== null) {
            $descripcion .= " " . json_encode($detalle_array);
        }

        $this->db->query('INSERT INTO bitacora (fecha, usuario, accion, detalle_json) VALUES (NOW(), :usuario, :accion, NULL)');

        $this->db->bind(':usuario', $usuario);
        $this->db->bind(':accion', $descripcion);

        return $this->db->execute();
    }
}
