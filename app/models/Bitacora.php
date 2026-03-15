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
        $this->db->query('INSERT INTO bitacora (fecha, usuario, accion, detalle_json) VALUES (NOW(), :usuario, :accion, :detalle)');

        $this->db->bind(':usuario', $usuario);
        $this->db->bind(':accion', $accion);
        $this->db->bind(':detalle', $detalle_array !== null ? json_encode($detalle_array) : null);

        return $this->db->execute();
    }
}