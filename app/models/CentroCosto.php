<?php
/**
 * Modelo: CentroCosto
 * Maneja el CRUD de centros de costo.
 */
class CentroCosto {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function listar(int $pagina = 1, int $porPagina = 10): array {
        $offset = ($pagina - 1) * $porPagina;
        $stmt = $this->db->prepare("
            SELECT id_centro_costo, codigo, nombre, descripcion, estado
            FROM centros_costo
            ORDER BY codigo ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM centros_costo");
        return (int) $stmt->fetchColumn();
    }

    public function obtener(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM centros_costo WHERE id_centro_costo = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function codigoExiste(string $codigo, int $idIgnorar = 0): bool {
        $sql = "SELECT id_centro_costo FROM centros_costo WHERE codigo = :codigo";
        if ($idIgnorar > 0) {
            $sql .= " AND id_centro_costo != :id";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':codigo', $codigo);
        if ($idIgnorar > 0) {
            $stmt->bindValue(':id', $idIgnorar, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    public function crear(array $datos): int {
        $stmt = $this->db->prepare("
            INSERT INTO centros_costo (codigo, nombre, descripcion, estado)
            VALUES (:codigo, :nombre, :descripcion, :estado)
        ");
        $stmt->execute([
            ':codigo'      => $datos['codigo'],
            ':nombre'      => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] ?? null,
            ':estado'      => $datos['estado'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool {
        $stmt = $this->db->prepare("
            UPDATE centros_costo
            SET codigo = :codigo,
                nombre = :nombre,
                descripcion = :descripcion,
                estado = :estado
            WHERE id_centro_costo = :id
        ");
        return $stmt->execute([
            ':codigo'      => $datos['codigo'],
            ':nombre'      => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] ?? null,
            ':estado'      => $datos['estado'] ?? 1,
            ':id'          => $id,
        ]);
    }

    public function tieneRelaciones(int $id): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM prorrateo_centro_costo WHERE id_centro_costo = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function eliminar(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM centros_costo WHERE id_centro_costo = :id");
        return $stmt->execute([':id' => $id]);
    }
}
