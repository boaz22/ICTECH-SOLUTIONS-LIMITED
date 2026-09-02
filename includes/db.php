<?php
/**
 * ICTECH Solutions - Database Connection
 * PDO connection with error handling
 */

require_once __DIR__ . '/config.php';

class Database {
    private $pdo;
    private static $instance = null;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Unable to connect to the database. Please try again later.");
            }
        }
    }

    // Singleton pattern
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Get PDO instance
    public function getConnection() {
        return $this->pdo;
    }

    // Execute a query
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (DEBUG_MODE && LOG_ERRORS) {
                error_log("Database query error: " . $e->getMessage());
            }
            throw $e;
        }
    }

    // Get single row
    public function getRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    // Get all rows
    public function getAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    // Get single value
    public function getValue($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }

    // Insert record
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $stmt = $this->query($sql, array_values($data));
        return $this->pdo->lastInsertId();
    }

    // Update record
    public function update($table, $data, $where = '', $whereParams = []) {
        $set = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
        $sql = "UPDATE $table SET $set";
        
        if ($where) {
            $sql .= " WHERE $where";
        }
        
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }

    // Delete record
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    // Count records
    public function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        return $this->getValue($sql, $params);
    }

    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserializing
    public function __wakeup() {
        throw new Exception("Cannot unserialize Database");
    }
}

// Get database instance
$db = Database::getInstance();

?>
