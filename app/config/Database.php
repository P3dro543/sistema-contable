<?php
/**
 * Database.php
 * Singleton de conexión PDO.
 * Ajustar las constantes según el entorno.
 */
class Database {

    private static ?PDO $connection = null;

    // ── Configuración ──────────────────────────────────────────────────
    private static string $host     = 'tiusr15pl.cuc-carrera-ti.ac.cr';
    private static string $dbname   = 'tiusr15pl_sis_grupo2';
    private static string $username = 'Feli86ine';
    private static string $password = 'Feli86ine';
    private static string $charset  = 'utf8mb4';
    // ───────────────────────────────────────────────────────────────────

    private function __construct() {}

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$host,
                self::$dbname,
                self::$charset
            );

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connection = new PDO($dsn, self::$username, self::$password, $opciones);
            } catch (PDOException $e) {
                // No exponer detalles en producción
                error_log('Error de conexión BD: ' . $e->getMessage());
                http_response_code(500);
                die(json_encode(['error' => 'Error de conexión con la base de datos.']));
            }
        }

        return self::$connection;
    }
}