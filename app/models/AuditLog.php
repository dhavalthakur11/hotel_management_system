<?php
class AuditLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function log($userId, $action, $tableName, $recordId, $description) {
        $sql = "INSERT INTO audit_logs (audit_id, user_id, action, table_name,
                record_id, description, ip_address, created_at) VALUES 
                (audit_seq.NEXTVAL, :user_id, :action, :table_name, :record_id,
                :description, :ip_address, SYSDATE)";
        try {
            return $this->db->execute($sql, [
                ':user_id' => $userId,
                ':action' => $action,
                ':table_name' => $tableName,
                ':record_id' => $recordId,
                ':description' => $description,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT a.*, u.username, u.full_name 
                FROM audit_logs a
                LEFT JOIN users u ON a.user_id = u.user_id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND a.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND a.action = :action";
            $params[':action'] = $filters['action'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND a.created_at >= TO_DATE(:date_from, 'YYYY-MM-DD')";
            $params[':date_from'] = $filters['date_from'];
        }
        
        $sql .= " ORDER BY a.created_at DESC";
        
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>