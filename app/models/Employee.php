<?php
require_once __DIR__ . '/../config/database.php';

class Employee {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Get all employees
    public function getAll($filters = []) {
        $sql = "SELECT e.*, u.username, u.email, u.role 
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.user_id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['department'])) {
            $sql .= " AND e.department = :department";
            $params[':department'] = $filters['department'];
        }
        
        if (!empty($filters['designation'])) {
            $sql .= " AND e.designation = :designation";
            $params[':designation'] = $filters['designation'];
        }
        
        $sql .= " ORDER BY e.created_at DESC";
        
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Get employees error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get employee by ID
    public function getById($employeeId) {
        $sql = "SELECT e.*, u.username, u.email, u.role 
                FROM employees e
                LEFT JOIN users u ON e.user_id = u.user_id
                WHERE e.employee_id = :employee_id";
        $params = [':employee_id' => $employeeId];
        
        try {
            $results = $this->db->query($sql, $params);
            return count($results) > 0 ? $results[0] : null;
        } catch (Exception $e) {
            error_log("Get employee error: " . $e->getMessage());
            return null;
        }
    }
    
    // Create employee
    public function create($data) {
        $sql = "INSERT INTO employees (employee_id, user_id, full_name, phone, 
                department, designation, salary, joining_date, shift_timing, created_at)
                VALUES (employee_seq.NEXTVAL, :user_id, :full_name, :phone,
                :department, :designation, :salary, TO_DATE(:joining_date, 'YYYY-MM-DD'), 
                :shift_timing, SYSDATE)";
        
        $params = [
            ':user_id' => $data['user_id'],
            ':full_name' => $data['full_name'],
            ':phone' => $data['phone'],
            ':department' => $data['department'],
            ':designation' => $data['designation'],
            ':salary' => $data['salary'],
            ':joining_date' => $data['joining_date'],
            ':shift_timing' => $data['shift_timing'] ?? null
        ];
        
        try {
            $this->db->execute($sql, $params);
            return $this->db->lastInsertId('employee_seq');
        } catch (Exception $e) {
            error_log("Create employee error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update employee
    public function update($employeeId, $data) {
        $sql = "UPDATE employees SET 
                full_name = :full_name,
                phone = :phone,
                department = :department,
                designation = :designation,
                salary = :salary,
                shift_timing = :shift_timing,
                updated_at = SYSDATE
                WHERE employee_id = :employee_id";
        
        $params = [
            ':employee_id' => $employeeId,
            ':full_name' => $data['full_name'],
            ':phone' => $data['phone'],
            ':department' => $data['department'],
            ':designation' => $data['designation'],
            ':salary' => $data['salary'],
            ':shift_timing' => $data['shift_timing'] ?? null
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Update employee error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete employee
    public function delete($employeeId) {
        $sql = "DELETE FROM employees WHERE employee_id = :employee_id";
        $params = [':employee_id' => $employeeId];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Delete employee error: " . $e->getMessage());
            return false;
        }
    }
}
?>