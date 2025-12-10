<?php
// Database Configuration and Connection Handler

class Database {
    private static $instance = null;
    private $connection;
    
    // Oracle Database Credentials
    private $host = "localhost";
    private $port = "1521";
    private $service_name = "XE"; // Change to your service name
    private $username = "hotel_user";
    private $password = "hotel_pass";
    
    private function __construct() {
        $this->connect();
    }
    
    // Singleton pattern to ensure single database connection
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    // Establish connection to Oracle database
    private function connect() {
        try {
            $connection_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST={$this->host})(PORT={$this->port}))(CONNECT_DATA=(SERVICE_NAME={$this->service_name})))";
            
            $this->connection = oci_connect(
                $this->username,
                $this->password,
                $connection_string,
                'AL32UTF8'
            );
            
            if (!$this->connection) {
                $error = oci_error();
                throw new Exception("Database connection failed: " . $error['message']);
            }
        } catch (Exception $e) {
            die("Database Error: " . $e->getMessage());
        }
    }
    
    // Get database connection
    public function getConnection() {
        return $this->connection;
    }
    
    // Execute SELECT query
    public function query($sql, $params = []) {
        $stmt = oci_parse($this->connection, $sql);
        
        if (!$stmt) {
            $error = oci_error($this->connection);
            throw new Exception("Query parsing failed: " . $error['message']);
        }
        
        // Bind parameters
        foreach ($params as $key => $value) {
            oci_bind_by_name($stmt, $key, $params[$key]);
        }
        
        // Execute query
        $result = oci_execute($stmt);
        
        if (!$result) {
            $error = oci_error($stmt);
            throw new Exception("Query execution failed: " . $error['message']);
        }
        
        // Fetch all results
        $rows = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $rows[] = $row;
        }
        
        oci_free_statement($stmt);
        return $rows;
    }
    
    // Execute INSERT, UPDATE, DELETE queries
    public function execute($sql, $params = []) {
        $stmt = oci_parse($this->connection, $sql);
        
        if (!$stmt) {
            $error = oci_error($this->connection);
            throw new Exception("Query parsing failed: " . $error['message']);
        }
        
        // Bind parameters
        foreach ($params as $key => $value) {
            oci_bind_by_name($stmt, $key, $params[$key]);
        }
        
        // Execute query
        $result = oci_execute($stmt, OCI_NO_AUTO_COMMIT);
        
        if (!$result) {
            $error = oci_error($stmt);
            oci_free_statement($stmt);
            throw new Exception("Query execution failed: " . $error['message']);
        }
        
        oci_commit($this->connection);
        oci_free_statement($stmt);
        
        return true;
    }
    
    // Get last inserted ID (for auto-increment columns)
    public function lastInsertId($sequence_name) {
        $sql = "SELECT {$sequence_name}.CURRVAL FROM DUAL";
        $result = $this->query($sql);
        return $result[0]['CURRVAL'];
    }
    
    // Begin transaction
    public function beginTransaction() {
        // Oracle doesn't need explicit BEGIN
        return true;
    }
    
    // Commit transaction
    public function commit() {
        return oci_commit($this->connection);
    }
    
    // Rollback transaction
    public function rollback() {
        return oci_rollback($this->connection);
    }
    
    // Escape string to prevent SQL injection
    public function escape($value) {
        return str_replace("'", "''", $value);
    }
    
    // Close connection
    public function close() {
        if ($this->connection) {
            oci_close($this->connection);
            $this->connection = null;
        }
    }
    
    // Destructor
    public function __destruct() {
        $this->close();
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function to get database instance
function getDB() {
    return Database::getInstance();
}
?>