<?php
/**
 * Database Configuration
 * PBO Compliance Platform - CRECO Kenya
 * 
 * MySQL DB Name:     if0_42280606_if0_42280606_
 * MySQL User Name:   if0_42280606
 * MySQL Password:    AES256:4m0deNaMM0HA+yKw/HIgbYzFLvAjq8o1cD7cfheTaOSB8M/MqTc/Edx85mfbuzOL
 * MySQL Host Name:   sql303.infinityfree.com
 * PHPMyAdmin:        Available via vPanel dashboard
 */

define('DB_HOST', 'sql303.infinityfree.com');
define('DB_NAME', 'if0_42280606_if0_42280606_');
define('DB_USER', 'if0_42280606');
define('DB_PASS', 'AES256:4m0deNaMM0HA+yKw/HIgbYzFLvAjq8o1cD7cfheTaOSB8M/MqTc/Edx85mfbuzOL'); // Replace with actual vPanel password
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
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
            die(json_encode([
                'error' => true,
                'message' => 'Database connection failed. Please try again later.'
            ]));
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