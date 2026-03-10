<?php

class Tercero
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Obtener todos los terceros (con paginacion y busqueda)
    public function getTercerosPaginados($limit, $offset, $search = '')
    {
        $sql = "SELECT * FROM terceros ";
        if (!empty($search)) {
            $sql .= "WHERE nombre LIKE :search OR identificacion LIKE :search ";
        }
        $sql .= "ORDER BY nombre ASC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', '%' . $search . '%');
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    // Contar total de registros para paginacion
    public function getTotalTerceros($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM terceros ";
        if (!empty($search)) {
            $sql .= "WHERE nombre LIKE :search OR identificacion LIKE :search ";
        }

        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', '%' . $search . '%');
        }

        $row = $this->db->single();
        return $row->total;
    }

    // Obtener un tercero por ID
    public function getTerceroById($id)
    {
        $this->db->query("SELECT * FROM terceros WHERE id_tercero = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Comprobar si identificación ya existe
    public function identificacionExiste($identificacion, $idIgnore = null)
    {
        $sql = "SELECT id_tercero FROM terceros WHERE identificacion = :identificacion";
        if ($idIgnore) {
            $sql .= " AND id_tercero != :idIgnore";
        }

        $this->db->query($sql);
        $this->db->bind(':identificacion', $identificacion);
        if ($idIgnore) {
            $this->db->bind(':idIgnore', $idIgnore);
        }

        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    // Crear un nuevo tercero
    public function agregarTercero($datos)
    {
        $this->db->query('INSERT INTO terceros (identificacion, nombre, tipo, correo, telefono, estado) 
                          VALUES (:identificacion, :nombre, :tipo, :correo, :telefono, :estado)');

        // Vincular valores
        $this->db->bind(':identificacion', $datos['identificacion']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':tipo', $datos['tipo']);
        $this->db->bind(':correo', $datos['correo']);
        $this->db->bind(':telefono', $datos['telefono']);
        $this->db->bind(':estado', $datos['estado']);

        // Ejecutar
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Actualizar tercero
    public function actualizarTercero($datos)
    {
        $this->db->query('UPDATE terceros SET identificacion = :identificacion, nombre = :nombre, 
                          tipo = :tipo, correo = :correo, telefono = :telefono, estado = :estado 
                          WHERE id_tercero = :id_tercero');

        // Vincular valores
        $this->db->bind(':id_tercero', $datos['id_tercero']);
        $this->db->bind(':identificacion', $datos['identificacion']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':tipo', $datos['tipo']);
        $this->db->bind(':correo', $datos['correo']);
        $this->db->bind(':telefono', $datos['telefono']);
        $this->db->bind(':estado', $datos['estado']);

        // Ejecutar
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Eliminar tercero
    public function eliminarTercero($id)
    {
        $this->db->query('DELETE FROM terceros WHERE id_tercero = :id');
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
