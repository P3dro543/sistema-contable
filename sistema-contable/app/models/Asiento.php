<?php

class Asiento
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    private function getColumnNames($table)
    {
        try {
            $this->db->query("SHOW COLUMNS FROM `$table`");
            $cols = $this->db->resultSet();
            return array_map(function ($c) {
                return strtolower($c->Field);
            }, $cols);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAsientosPaginados($limit, $offset, $idPeriodo = null)
    {
        $cols = $this->getColumnNames('asientos');
        $tienePeriodo = in_array('id_periodo', $cols);

        $sql = "SELECT a.*, ea.nombre AS estado_nombre
                FROM asientos a
                LEFT JOIN estados_asiento ea ON a.id_estado = ea.id_estado ";

        if ($idPeriodo !== null && $tienePeriodo) {
            $sql .= "WHERE a.id_periodo = :id_periodo ";
        }

        $sql .= "ORDER BY a.id_asiento DESC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        if ($idPeriodo !== null && $tienePeriodo) {
            $this->db->bind(':id_periodo', $idPeriodo);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function getTotalAsientos($idPeriodo = null)
    {
        $cols = $this->getColumnNames('asientos');
        $tienePeriodo = in_array('id_periodo', $cols);

        $sql = "SELECT COUNT(*) as total FROM asientos ";
        if ($idPeriodo !== null && $tienePeriodo) {
            $sql .= "WHERE id_periodo = :id_periodo";
        }

        $this->db->query($sql);
        if ($idPeriodo !== null && $tienePeriodo) {
            $this->db->bind(':id_periodo', $idPeriodo);
        }
        $row = $this->db->single();
        return $row ? $row->total : 0;
    }

    public function getDetallesPorAsiento($id_asiento)
    {
        // En el esquema real: asiento_detalle no tiene descripcion; se une con cuentas_contables.
        $this->db->query("SELECT d.*, c.codigo AS cuenta_codigo, c.nombre AS cuenta_nombre
                          FROM asiento_detalle d
                          JOIN cuentas_contables c ON d.id_cuenta = c.id_cuenta
                          WHERE d.id_asiento = :id_asiento
                          ORDER BY d.id_detalle ASC");
        $this->db->bind(':id_asiento', $id_asiento);
        return $this->db->resultSet();
    }
}
