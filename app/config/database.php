<?php
// Database Configuration and Connection Handler

class Database {
    private static $instance = null;
    private $connection;
    
    // Oracle Database Credentials
    private $host = "10.147.17.170";
    private $port = "1521";
    private $service_name = "orclpdb";
    private $username = "system";
    private $password = 'dhaval123';
    
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
            // Set Oracle environment variables
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                putenv("ORACLE_HOME=C:\\oracle\\product\\21c\\dbhomeXE");
                putenv("TNS_ADMIN=C:\\oracle\\product\\21c\\dbhomeXE\\network\\admin");
            }
            
            // Connection timeout - 5 seconds
            ini_set('default_socket_timeout', 5);
            
            // Try Easy Connect with timeout
            $easy_connect = "{$this->host}:{$this->port}/{$this->service_name}";
            
            echo "Attempting connection to: $easy_connect<br>";
            
            $this->connection = @oci_connect(
                $this->username,
                $this->password,
                $easy_connect,
                'AL32UTF8'
            );
            
            if (!$this->connection) {
                $error = oci_error();
                throw new Exception("Connection failed: " . $error['message']);
            }
            
            echo "✓ Connected successfully!<br>";
            
        } catch (Exception $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }
    
    // Get database connection
    public function getConnection() {
        return $this->connection;
    }
    
    // Execute SELECT query
    public function query($sql, $params = []) {
        try {
            $stmt = oci_parse($this->connection, $sql);
            
            if (!$stmt) {
                $error = oci_error($this->connection);
                throw new Exception("Parse Error: " . $error['message']);
            }
            
            // Bind parameters
            foreach ($params as $key => $value) {
                oci_bind_by_name($stmt, $key, $params[$key]);
            }
            
            // Execute query
            $result = oci_execute($stmt, OCI_DESCRIBE_ONLY);
            
            if (!$result) {
                $error = oci_error($stmt);
                throw new Exception("Execute Error: " . $error['message']);
            }
            
            oci_execute($stmt);
            
            // Fetch all results
            $rows = [];
            while ($row = oci_fetch_assoc($stmt)) {
                $rows[] = $row;
            }
            
            oci_free_statement($stmt);
            return $rows;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    
    // Execute INSERT, UPDATE, DELETE queries
    public function execute($sql, $params = []) {
        try {
            $stmt = oci_parse($this->connection, $sql);
            
            if (!$stmt) {
                $error = oci_error($this->connection);
                throw new Exception("Parse Error: " . $error['message']);
            }
            
            // Bind parameters
            foreach ($params as $key => $value) {
                oci_bind_by_name($stmt, $key, $params[$key]);
            }
            
            // Execute query
            $result = oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
            
            if (!$result) {
                $error = oci_error($stmt);
                oci_free_statement($stmt);
                throw new Exception("Execute Error: " . $error['message']);
            }
            
            oci_free_statement($stmt);
            return true;
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
    
    // Get last inserted ID
    public function lastInsertId($sequence_name) {
        try {
            $sql = "SELECT {$sequence_name}.CURRVAL as id FROM DUAL";
            $result = $this->query($sql);
            return isset($result[0]['ID']) ? $result[0]['ID'] : null;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }
    
    // Escape string
    public function escape($value) {
        return str_replace("'", "''", $value);
    }
    
    // Close connection
    public function close() {
        if ($this->connection) {
            oci_close($this->connection);
        }
    }
    
    public function __destruct() {
        $this->close();
    }
    
    private function __clone() {}
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function
function getDB() {
    return Database::getInstance();
}
?>