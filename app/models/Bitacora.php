<?php
/**
 * Modelo: Bitacora
 * Registra todas las acciones importantes del sistema.
 */
class Bitacora {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Registra una acción en la bitácora.
     *
     * @param string $usuario  Username del usuario que ejecuta la acción.
     * @param string $accion   Descripción corta de la acción.
     * @param mixed  $detalle  Datos en formato array que se serializarán a JSON.
     */
    public function registrar(string $usuario, string $accion, mixed $detalle = null): void {
        $stmt = $this->db->prepare("
            INSERT INTO bitacora (fecha, usuario, accion, detalle_json)
            VALUES (NOW(), :usuario, :accion, :detalle)
        ");
        $stmt->execute([
            ':usuario' => $usuario,
            ':accion'  => $accion,
            ':detalle' => $detalle !== null ? json_encode($detalle, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }
}