<?php
class Database {
    private static ?PDO $connection = null;
    private $stmt;

    // ── Credenciales Localhost ──────────────────────────────────────────
    private static string $host     = 'localhost';
    private static string $dbname   = 'sistema_contable';
    private static string $username = 'root';
    private static string $password = '1234';
    private static string $charset  = 'utf8mb4';

    public function __construct() {}

    // ESTO SE QUEDA IGUAL: Para que el Login y lo viejo no falle
    public static function getConnection(): PDO {
        if (self::$connection === null) {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', self::$host, self::$dbname, self::$charset);
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, // Cambiado a OBJ para que el modelo funcione
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$connection = new PDO($dsn, self::$username, self::$password, $opciones);
            } catch (PDOException $e) {
                error_log('Error de conexión BD: ' . $e->getMessage());
                die("Error de conexión con la base de datos.");
            }
        }
        return self::$connection;
    }

    // ── MÉTODOS EXTRA: Para que el modelo Tercero funcione ──────────────
    
    public function query($sql) {
        $this->stmt = self::getConnection()->prepare($sql);
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() {
        return $this->stmt->execute();
    }

    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function lastInsertId() {
        return self::getConnection()->lastInsertId();
    }
}