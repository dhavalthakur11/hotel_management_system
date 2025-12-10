<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class Room {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Get all rooms
    public function getAll($filters = []) {
        $sql = "SELECT r.*, t.tariff_name, t.base_price 
                FROM rooms r
                LEFT JOIN tariffs t ON r.tariff_id = t.tariff_id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['room_type'])) {
            $sql .= " AND r.room_type = :room_type";
            $params[':room_type'] = $filters['room_type'];
        }
        
        if (!empty($filters['floor'])) {
            $sql .= " AND r.floor_number = :floor";
            $params[':floor'] = $filters['floor'];
        }
        
        $sql .= " ORDER BY r.room_number";
        
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Get rooms error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get room by ID
    public function getById($roomId) {
        $sql = "SELECT r.*, t.tariff_name, t.base_price 
                FROM rooms r
                LEFT JOIN tariffs t ON r.tariff_id = t.tariff_id
                WHERE r.room_id = :room_id";
        $params = [':room_id' => $roomId];
        
        try {
            $results = $this->db->query($sql, $params);
            return count($results) > 0 ? $results[0] : null;
        } catch (Exception $e) {
            error_log("Get room error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get available rooms for date range
    public function getAvailableRooms($checkIn, $checkOut, $roomType = null) {
        $sql = "SELECT r.*, t.tariff_name, t.base_price 
                FROM rooms r
                LEFT JOIN tariffs t ON r.tariff_id = t.tariff_id
                WHERE r.status = :status
                AND r.room_id NOT IN (
                    SELECT b.room_id FROM bookings b
                    WHERE b.status NOT IN (:cancelled)
                    AND (
                        (b.check_in_date <= TO_DATE(:check_out, 'YYYY-MM-DD') 
                         AND b.check_out_date >= TO_DATE(:check_in, 'YYYY-MM-DD'))
                    )
                )";
        
        $params = [
            ':status' => ROOM_AVAILABLE,
            ':cancelled' => BOOKING_CANCELLED,
            ':check_in' => $checkIn,
            ':check_out' => $checkOut
        ];
        
        if ($roomType) {
            $sql .= " AND r.room_type = :room_type";
            $params[':room_type'] = $roomType;
        }
        
        $sql .= " ORDER BY r.room_number";
        
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Get available rooms error: " . $e->getMessage());
            return [];
        }
    }
    
    // Create room
    public function create($data) {
        $sql = "INSERT INTO rooms (room_id, room_number, room_type, floor_number, 
                max_occupancy, tariff_id, status, amenities, description, created_at)
                VALUES (room_seq.NEXTVAL, :room_number, :room_type, :floor_number,
                :max_occupancy, :tariff_id, :status, :amenities, :description, SYSDATE)";
        
        $params = [
            ':room_number' => $data['room_number'],
            ':room_type' => $data['room_type'],
            ':floor_number' => $data['floor_number'],
            ':max_occupancy' => $data['max_occupancy'],
            ':tariff_id' => $data['tariff_id'],
            ':status' => $data['status'] ?? ROOM_AVAILABLE,
            ':amenities' => $data['amenities'] ?? null,
            ':description' => $data['description'] ?? null
        ];
        
        try {
            $this->db->execute($sql, $params);
            return $this->db->lastInsertId('room_seq');
        } catch (Exception $e) {
            error_log("Create room error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update room
    public function update($roomId, $data) {
        $sql = "UPDATE rooms SET 
                room_number = :room_number,
                room_type = :room_type,
                floor_number = :floor_number,
                max_occupancy = :max_occupancy,
                tariff_id = :tariff_id,
                status = :status,
                amenities = :amenities,
                description = :description,
                updated_at = SYSDATE
                WHERE room_id = :room_id";
        
        $params = [
            ':room_id' => $roomId,
            ':room_number' => $data['room_number'],
            ':room_type' => $data['room_type'],
            ':floor_number' => $data['floor_number'],
            ':max_occupancy' => $data['max_occupancy'],
            ':tariff_id' => $data['tariff_id'],
            ':status' => $data['status'],
            ':amenities' => $data['amenities'] ?? null,
            ':description' => $data['description'] ?? null
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Update room error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update room status
    public function updateStatus($roomId, $status) {
        $sql = "UPDATE rooms SET status = :status, updated_at = SYSDATE WHERE room_id = :room_id";
        $params = [
            ':room_id' => $roomId,
            ':status' => $status
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Update room status error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete room
    public function delete($roomId) {
        $sql = "DELETE FROM rooms WHERE room_id = :room_id";
        $params = [':room_id' => $roomId];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Delete room error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get room statistics
    public function getStatistics() {
        $sql = "SELECT 
                COUNT(*) as total_rooms,
                SUM(CASE WHEN status = :available THEN 1 ELSE 0 END) as available_rooms,
                SUM(CASE WHEN status = :occupied THEN 1 ELSE 0 END) as occupied_rooms,
                SUM(CASE WHEN status = :maintenance THEN 1 ELSE 0 END) as maintenance_rooms,
                SUM(CASE WHEN status = :reserved THEN 1 ELSE 0 END) as reserved_rooms
                FROM rooms";
        
        $params = [
            ':available' => ROOM_AVAILABLE,
            ':occupied' => ROOM_OCCUPIED,
            ':maintenance' => ROOM_MAINTENANCE,
            ':reserved' => ROOM_RESERVED
        ];
        
        try {
            $result = $this->db->query($sql, $params);
            return $result[0];
        } catch (Exception $e) {
            error_log("Get room statistics error: " . $e->getMessage());
            return null;
        }
    }
    
    // Check if room number exists
    public function roomNumberExists($roomNumber, $excludeRoomId = null) {
        $sql = "SELECT COUNT(*) as cnt FROM rooms WHERE room_number = :room_number";
        $params = [':room_number' => $roomNumber];
        
        if ($excludeRoomId) {
            $sql .= " AND room_id != :room_id";
            $params[':room_id'] = $excludeRoomId;
        }
        
        try {
            $result = $this->db->query($sql, $params);
            return $result[0]['CNT'] > 0;
        } catch (Exception $e) {
            error_log("Room number exists check error: " . $e->getMessage());
            return false;
        }
    }
}
?>