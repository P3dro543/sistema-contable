<?php
/**
 * Modelo: Usuario
 * Maneja autenticación y datos de usuarios del sistema.
 */
class Usuario {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Busca un usuario por username.
     */
    public function buscarPorUsername(string $username): array|false {
        $stmt = $this->db->prepare("
            SELECT u.id_usuario, u.username, u.nombre, u.apellido,
                   u.correo, u.password, u.estado
            FROM usuarios u
            WHERE u.username = :username
        ");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los roles de un usuario.
     */
    public function obtenerRoles(int $idUsuario): array {
        $stmt = $this->db->prepare("
            SELECT r.id_rol, r.nombre
            FROM roles r
            INNER JOIN usuario_rol ur ON ur.id_rol = r.id_rol
            WHERE ur.id_usuario = :id
        ");
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las pantallas (rutas) permitidas para un usuario según sus roles.
     */
    public function obtenerPantallas(int $idUsuario): array {
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.id_pantalla, p.nombre, p.ruta
            FROM pantallas p
            INNER JOIN rol_pantalla rp ON rp.id_pantalla = p.id_pantalla
            INNER JOIN usuario_rol ur ON ur.id_rol = rp.id_rol
            WHERE ur.id_usuario = :id AND p.estado = 1
            ORDER BY p.nombre
        ");
        $stmt->execute([':id' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}