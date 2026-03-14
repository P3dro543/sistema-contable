<?php

class CentroCosto
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getCentrosCostoPaginados($limit, $offset, $search = '')
    {
        $sql = "SELECT * FROM centros_costo ";
        if (!empty($search)) {
            $sql .= "WHERE codigo LIKE :search OR nombre LIKE :search ";
        }
        $sql .= "ORDER BY codigo ASC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', '%' . $search . '%');
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function getTotalCentrosCosto($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM centros_costo ";
        if (!empty($search)) {
            $sql .= "WHERE codigo LIKE :search OR nombre LIKE :search ";
        }

        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', '%' . $search . '%');
        }

        $row = $this->db->single();
        return $row ? $row->total : 0;
    }

    public function getCentroCostoById($id)
    {
        $this->db->query("SELECT * FROM centros_costo WHERE id_centro_costo = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function codigoExiste($codigo, $idIgnore = null)
    {
        $sql = "SELECT id_centro_costo FROM centros_costo WHERE codigo = :codigo";
        if ($idIgnore) {
            $sql .= " AND id_centro_costo != :idIgnore";
        }

        $this->db->query($sql);
        $this->db->bind(':codigo', $codigo);
        if ($idIgnore) {
            $this->db->bind(':idIgnore', $idIgnore);
        }

        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function agregarCentroCosto($datos)
    {
        $this->db->query('INSERT INTO centros_costo (codigo, nombre, descripcion, estado) 
                          VALUES (:codigo, :nombre, :descripcion, :estado)');

        $this->db->bind(':codigo', $datos['codigo']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':descripcion', $datos['descripcion']);
        $this->db->bind(':estado', $datos['estado']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function actualizarCentroCosto($datos)
    {
        $this->db->query('UPDATE centros_costo SET codigo = :codigo, nombre = :nombre, 
                          descripcion = :descripcion, estado = :estado 
                          WHERE id_centro_costo = :id_centro_costo');

        $this->db->bind(':id_centro_costo', $datos['id_centro_costo']);
        $this->db->bind(':codigo', $datos['codigo']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':descripcion', $datos['descripcion']);
        $this->db->bind(':estado', $datos['estado']);

        return $this->db->execute();
    }

    public function eliminarCentroCosto($id)
    {
        $this->db->query('DELETE FROM centros_costo WHERE id_centro_costo = :id');
        $this->db->bind(':id', $id);

        try {
            if ($this->db->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            // Error de llave foránea (1451)
            if ($e->getCode() == '23000' || strpos($e->getMessage(), '1451') !== false) {
                return "relacionado";
            }
        }
        return false;
    }
}

