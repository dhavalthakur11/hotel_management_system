<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class Customer {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Get all customers
    public function getAll($search = '') {
        $sql = "SELECT * FROM customers WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (full_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Get customers error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get customer by ID
    public function getById($customerId) {
        $sql = "SELECT * FROM customers WHERE customer_id = :customer_id";
        $params = [':customer_id' => $customerId];
        
        try {
            $results = $this->db->query($sql, $params);
            return count($results) > 0 ? $results[0] : null;
        } catch (Exception $e) {
            error_log("Get customer error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get customer by user ID
    public function getByUserId($userId) {
        $sql = "SELECT * FROM customers WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        
        try {
            $results = $this->db->query($sql, $params);
            return count($results) > 0 ? $results[0] : null;
        } catch (Exception $e) {
            error_log("Get customer by user error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get customer by email
    public function getByEmail($email) {
        $sql = "SELECT * FROM customers WHERE email = :email";
        $params = [':email' => $email];
        
        try {
            $results = $this->db->query($sql, $params);
            return count($results) > 0 ? $results[0] : null;
        } catch (Exception $e) {
            error_log("Get customer by email error: " . $e->getMessage());
            return null;
        }
    }
    
    // Create customer
    public function create($data) {
        $sql = "INSERT INTO customers (customer_id, user_id, full_name, email, phone, 
                address, city, state, country, id_proof_type, id_proof_number, created_at)
                VALUES (customer_seq.NEXTVAL, :user_id, :full_name, :email, :phone, 
                :address, :city, :state, :country, :id_proof_type, :id_proof_number, SYSDATE)";
        
        $params = [
            ':user_id' => $data['user_id'] ?? null,
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':address' => $data['address'] ?? null,
            ':city' => $data['city'] ?? null,
            ':state' => $data['state'] ?? null,
            ':country' => $data['country'] ?? null,
            ':id_proof_type' => $data['id_proof_type'] ?? null,
            ':id_proof_number' => $data['id_proof_number'] ?? null
        ];
        
        try {
            $this->db->execute($sql, $params);
            return $this->db->lastInsertId('customer_seq');
        } catch (Exception $e) {
            error_log("Create customer error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update customer
    public function update($customerId, $data) {
        $sql = "UPDATE customers SET 
                full_name = :full_name,
                email = :email,
                phone = :phone,
                address = :address,
                city = :city,
                state = :state,
                country = :country,
                id_proof_type = :id_proof_type,
                id_proof_number = :id_proof_number,
                updated_at = SYSDATE
                WHERE customer_id = :customer_id";
        
        $params = [
            ':customer_id' => $customerId,
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':address' => $data['address'] ?? null,
            ':city' => $data['city'] ?? null,
            ':state' => $data['state'] ?? null,
            ':country' => $data['country'] ?? null,
            ':id_proof_type' => $data['id_proof_type'] ?? null,
            ':id_proof_number' => $data['id_proof_number'] ?? null
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Update customer error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete customer
    public function delete($customerId) {
        $sql = "DELETE FROM customers WHERE customer_id = :customer_id";
        $params = [':customer_id' => $customerId];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Delete customer error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get customer bookings
    public function getBookings($customerId) {
        $sql = "SELECT b.*, r.room_number, r.room_type 
                FROM bookings b
                LEFT JOIN rooms r ON b.room_id = r.room_id
                WHERE b.customer_id = :customer_id
                ORDER BY b.created_at DESC";
        $params = [':customer_id' => $customerId];
        
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Get customer bookings error: " . $e->getMessage());
            return [];
        }
    }
    
    // Check if email exists
    public function emailExists($email, $excludeCustomerId = null) {
        $sql = "SELECT COUNT(*) as cnt FROM customers WHERE email = :email";
        $params = [':email' => $email];
        
        if ($excludeCustomerId) {
            $sql .= " AND customer_id != :customer_id";
            $params[':customer_id'] = $excludeCustomerId;
        }
        
        try {
            $result = $this->db->query($sql, $params);
            return $result[0]['CNT'] > 0;
        } catch (Exception $e) {
            error_log("Email exists check error: " . $e->getMessage());
            return false;
        }
    }
    
    // Check if phone exists
    public function phoneExists($phone, $excludeCustomerId = null) {
        $sql = "SELECT COUNT(*) as cnt FROM customers WHERE phone = :phone";
        $params = [':phone' => $phone];
        
        if ($excludeCustomerId) {
            $sql .= " AND customer_id != :customer_id";
            $params[':customer_id'] = $excludeCustomerId;
        }
        
        try {
            $result = $this->db->query($sql, $params);
            return $result[0]['CNT'] > 0;
        } catch (Exception $e) {
            error_log("Phone exists check error: " . $e->getMessage());
            return false;
        }
    }
}
?>