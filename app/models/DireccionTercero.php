<?php
/**
 * Modelo: DireccionTercero
 * Maneja las direcciones asociadas a un tercero.
 */
class DireccionTercero {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Lista las direcciones de un tercero (paginado).
     */
    public function listar(int $idTercero, int $pagina = 1, int $porPagina = 10): array {
        $offset = ($pagina - 1) * $porPagina;
        $stmt = $this->db->prepare("
            SELECT id_direccion, id_tercero, alias, provincia, canton,
                   distrito, direccion_exacta, estado, principal
            FROM direcciones_tercero
            WHERE id_tercero = :id
            ORDER BY principal DESC, id_direccion ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':id',     $idTercero, PDO::PARAM_INT);
        $stmt->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta el total de direcciones de un tercero.
     */
    public function contar(int $idTercero): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM direcciones_tercero WHERE id_tercero = :id
        ");
        $stmt->execute([':id' => $idTercero]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene una dirección por ID.
     */
    public function obtener(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT * FROM direcciones_tercero WHERE id_direccion = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva dirección.
     */
    public function crear(array $datos): int {
        // Si se marca como principal, desmarcar la anterior
        if (!empty($datos['principal'])) {
            $this->desmarcarPrincipal((int) $datos['id_tercero']);
        }

        $stmt = $this->db->prepare("
            INSERT INTO direcciones_tercero
                (id_tercero, alias, provincia, canton, distrito, direccion_exacta, estado, principal)
            VALUES
                (:id_tercero, :alias, :provincia, :canton, :distrito, :direccion_exacta, :estado, :principal)
        ");
        $stmt->execute([
            ':id_tercero'      => $datos['id_tercero'],
            ':alias'           => $datos['alias']           ?? null,
            ':provincia'       => $datos['provincia']       ?? null,
            ':canton'          => $datos['canton']          ?? null,
            ':distrito'        => $datos['distrito']        ?? null,
            ':direccion_exacta'=> $datos['direccion_exacta']?? null,
            ':estado'          => $datos['estado']          ?? 1,
            ':principal'       => $datos['principal']       ? 1 : 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza una dirección existente.
     */
    public function actualizar(int $id, array $datos): bool {
        // Si se marca como principal, desmarcar la anterior del mismo tercero
        if (!empty($datos['principal'])) {
            $dir = $this->obtener($id);
            if ($dir) {
                $this->desmarcarPrincipal((int) $dir['id_tercero'], $id);
            }
        }

        $stmt = $this->db->prepare("
            UPDATE direcciones_tercero SET
                alias            = :alias,
                provincia        = :provincia,
                canton           = :canton,
                distrito         = :distrito,
                direccion_exacta = :direccion_exacta,
                estado           = :estado,
                principal        = :principal
            WHERE id_direccion = :id
        ");
        return $stmt->execute([
            ':alias'           => $datos['alias']           ?? null,
            ':provincia'       => $datos['provincia']       ?? null,
            ':canton'          => $datos['canton']          ?? null,
            ':distrito'        => $datos['distrito']        ?? null,
            ':direccion_exacta'=> $datos['direccion_exacta']?? null,
            ':estado'          => $datos['estado']          ?? 1,
            ':principal'       => $datos['principal']       ? 1 : 0,
            ':id'              => $id,
        ]);
    }

    /**
     * Elimina una dirección si no tiene datos relacionados.
     * Retorna true si se eliminó, false si tiene relaciones.
     */
    public function eliminar(int $id): bool {
        // La tabla direcciones_tercero no tiene tablas hijas en el schema actual,
        // pero se verifica por consistencia futura.
        $stmt = $this->db->prepare("DELETE FROM direcciones_tercero WHERE id_direccion = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Desmarca el indicador principal de todas las direcciones de un tercero,
     * opcionalmente excluyendo un ID específico.
     */
    private function desmarcarPrincipal(int $idTercero, int $excluirId = 0): void {
        $stmt = $this->db->prepare("
            UPDATE direcciones_tercero
            SET principal = 0
            WHERE id_tercero = :id_tercero AND id_direccion != :excluir
        ");
        $stmt->execute([':id_tercero' => $idTercero, ':excluir' => $excluirId]);
    }
}