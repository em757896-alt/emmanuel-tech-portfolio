<?php
/**
 * Database Configuration
 * PBO Compliance Platform
 *
 * Credentials are NOT stored in this repository. They are loaded from
 * `config/local.php` (git-ignored) or from environment variables.
 * See `config/local.example.php` for the template.
 */

require_once __DIR__ . '/settings.php';

class Database {
    /** Marker message used to detect an unhandled connection failure. */
    public const CONNECTION_ERROR = 'Database connection failed';

    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            if (DB_HOST === '' || DB_NAME === '' || DB_USER === '' || DB_PASS === '') {
                throw new RuntimeException(
                    'Database credentials are missing. Copy config/local.example.php to config/local.php and fill it in.'
                );
            }

            $dsn = "mysql:host=" . DB_HOST . 
                   ";dbname=" . DB_NAME . 
                   ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_TIMEOUT            => 30,
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            // Thrown (not fatal) so that pages which do not need the database —
            // such as the legal pages — can still render. Callers that genuinely
            // need data are handled by the exception handler below.
            throw new RuntimeException(self::CONNECTION_ERROR, 0, $e);
        }
    }
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection(): PDO {
        return $this->conn;
    }
    
    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchOne(string $sql, array $params = []): ?array {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }
    
    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function insert(string $table, array $data): int {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);
        return (int)$this->conn->lastInsertId();
    }
    
    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $setParts = array_map(fn($col) => "`$col` = :$col", array_keys($data));
        $setClause = implode(', ', $setParts);
        $sql = "UPDATE `$table` SET $setClause WHERE $where";
        $stmt = $this->query($sql, array_merge($data, $whereParams));
        return $stmt->rowCount();
    }
    
    public function beginTransaction(): void {
        $this->conn->beginTransaction();
    }
    
    public function commit(): void {
        $this->conn->commit();
    }
    
    public function rollback(): void {
        $this->conn->rollBack();
    }
    
    // Prevent cloning
    private function __clone() {}
public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Renders a friendly response for database failures that reach the top level,
 * so visitors never see a PHP stack trace.
 */
set_exception_handler(function (Throwable $e): void {
    if (!headers_sent()) {
        http_response_code(503);
        header('Content-Type: application/json; charset=utf-8');
    }

    if ($e instanceof RuntimeException && $e->getMessage() === Database::CONNECTION_ERROR) {
        echo json_encode([
            'error'   => true,
            'message' => 'Database connection failed. Please try again later.'
        ]);
        return;
    }

    error_log('Unhandled error: ' . $e->getMessage());
    echo json_encode([
        'error'   => true,
        'message' => 'Something went wrong. Please try again later.'
    ]);
});
