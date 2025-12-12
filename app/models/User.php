<?php

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Authenticate user
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM users WHERE username = :username AND is_active = 1";
        $params = [':username' => $username];
        
        try {
            $results = $this->db->query($sql, $params);
            
            if (count($results) > 0) {
                $user = $results[0];
                // Verify password (assuming password is hashed with bcrypt/password_hash)
                if (password_verify($password, $user['PASSWORD'])) {
                    return $user;
                }
            }
            return false;
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get user by ID
    public function getById($userId) {
        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        
        try {
            $results = $this->db->query($sql, $params);
            return count($results) > 0 ? $results[0] : null;
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get all users
    public function getAll($role = null) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if ($role) {
            $sql .= " AND role = :role";
            $params[':role'] = $role;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Get all users error: " . $e->getMessage());
            return [];
        }
    }
    
    // Create new user
    public function create($data) {
        $sql = "INSERT INTO users (username, password, email, full_name, phone, role, is_active, created_at) 
                VALUES (:username, :password, :email, :full_name, :phone, :role, 1, SYSDATE)";
        
        $params = [
            ':username' => $data['username'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':email' => $data['email'],
            ':full_name' => $data['full_name'],
            ':phone' => $data['phone'],
            ':role' => $data['role']
        ];
        
        try {
            $this->db->execute($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("Create user error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update user
    public function update($userId, $data) {
        $sql = "UPDATE users SET 
                full_name = :full_name,
                email = :email,
                phone = :phone,
                role = :role,
                updated_at = SYSDATE
                WHERE user_id = :user_id";
        
        $params = [
            ':user_id' => $userId,
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':role' => $data['role']
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Update user error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update password
    public function updatePassword($userId, $newPassword) {
        $sql = "UPDATE users SET password = :password, updated_at = SYSDATE WHERE user_id = :user_id";
        $params = [
            ':user_id' => $userId,
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Update password error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete user (soft delete)
    public function delete($userId) {
        $sql = "UPDATE users SET is_active = 0, updated_at = SYSDATE WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Delete user error: " . $e->getMessage());
            return false;
        }
    }
    
    // Check if username exists
    public function usernameExists($username, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as cnt FROM users WHERE username = :username";
        $params = [':username' => $username];
        
        if ($excludeUserId) {
            $sql .= " AND user_id != :user_id";
            $params[':user_id'] = $excludeUserId;
        }
        
        try {
            $result = $this->db->query($sql, $params);
            return isset($result[0]['CNT']) && $result[0]['CNT'] > 0;
        } catch (Exception $e) {
            error_log("Username exists check error: " . $e->getMessage());
            return false;
        }
    }
    
    // Check if email exists
    public function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as cnt FROM users WHERE email = :email";
        $params = [':email' => $email];
        
        if ($excludeUserId) {
            $sql .= " AND user_id != :user_id";
            $params[':user_id'] = $excludeUserId;
        }
        
        try {
            $result = $this->db->query($sql, $params);
            return isset($result[0]['CNT']) && $result[0]['CNT'] > 0;
        } catch (Exception $e) {
            error_log("Email exists check error: " . $e->getMessage());
            return false;
        }
    }
}
?>