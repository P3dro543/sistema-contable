<?php

class Periodo
{
    private $db;
    private $periodoTable = null;

    public function __construct()
    {
        $this->db = new Database;
        $this->periodoTable = $this->detectarTablaPeriodos();
    }

    private function tableExists($table)
    {
        try {
            $this->db->query("SHOW TABLES LIKE :t");
            $this->db->bind(':t', $table);
            $row = $this->db->single();
            return (bool) $row;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function getColumnInfo($table)
    {
        try {
            $this->db->query("SHOW COLUMNS FROM `$table`");
            return $this->db->resultSet();
        } catch (PDOException $e) {
            return [];
        }
    }

    private function detectarTablaPeriodos()
    {
        $candidatas = ['periodos', 'periodos_contables', 'periodo_contable', 'periodo'];
        foreach ($candidatas as $t) {
            if ($this->tableExists($t)) {
                return $t;
            }
        }
        return null;
    }

    private function pickIdCol($cols)
    {
        foreach ($cols as $c) {
            if (strtolower($c->Field) === 'id_periodo') {
                return $c->Field;
            }
        }
        foreach ($cols as $c) {
            if (strpos(strtolower($c->Field), 'id_') === 0) {
                return $c->Field;
            }
        }
        return isset($cols[0]) ? $cols[0]->Field : null;
    }

    public function getPeriodos()
    {
        if (!$this->periodoTable) {
            return [];
        }

        $cols = $this->getColumnInfo($this->periodoTable);
        if (empty($cols)) {
            return [];
        }

        $fieldNames = array_map(function ($c) {
            return strtolower($c->Field);
        }, $cols);

        $idCol = $this->pickIdCol($cols);
        if (!$idCol) {
            return [];
        }

        $labelExpr = "`$idCol`";
        if (in_array('nombre', $fieldNames)) {
            $labelExpr = "nombre";
        } else if (in_array('descripcion', $fieldNames)) {
            $labelExpr = "descripcion";
        } else if (in_array('anio', $fieldNames) && in_array('mes', $fieldNames)) {
            $labelExpr = "CONCAT(anio, '-', LPAD(mes, 2, '0'))";
        }

        $sql = "SELECT `$idCol` AS id_periodo, $labelExpr AS nombre FROM `{$this->periodoTable}` ORDER BY `$idCol` DESC";
        try {
            $this->db->query($sql);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getPeriodoAbiertoActualId()
    {
        if (!$this->periodoTable) {
            return null;
        }

        $cols = $this->getColumnInfo($this->periodoTable);
        if (empty($cols)) {
            return null;
        }

        $fieldNames = array_map(function ($c) {
            return strtolower($c->Field);
        }, $cols);

        $idCol = $this->pickIdCol($cols);
        if (!$idCol) {
            return null;
        }

        // Heurística: preferir un flag "abierto", si no, un campo "estado".
        $where = null;
        if (in_array('abierto', $fieldNames)) {
            $where = "abierto = 1";
        } else if (in_array('estado', $fieldNames)) {
            // Si es numérico, asumir 1 = abierto; si es texto, buscar "abierto".
            $estadoCol = null;
            foreach ($cols as $c) {
                if (strtolower($c->Field) === 'estado') {
                    $estadoCol = $c;
                    break;
                }
            }
            $tipo = $estadoCol ? strtolower($estadoCol->Type) : '';
            if (strpos($tipo, 'int') !== false || strpos($tipo, 'tinyint') !== false) {
                $where = "estado = 1";
            } else {
                $where = "LOWER(TRIM(estado)) LIKE 'abierto%'";
            }
        }

        if (!$where) {
            return null;
        }

        try {
            // Si hay varios abiertos, tomar el más reciente.
            $this->db->query("SELECT `$idCol` AS id_periodo FROM `{$this->periodoTable}` WHERE $where ORDER BY `$idCol` DESC LIMIT 1");
            $row = $this->db->single();
            return $row ? $row->id_periodo : null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
