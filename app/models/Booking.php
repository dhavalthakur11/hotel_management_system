<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class Booking {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Get all bookings
    public function getAll($filters = []) {
        $sql = "SELECT b.*, c.full_name as customer_name, c.email, c.phone,
                r.room_number, r.room_type, u.full_name as created_by_name
                FROM bookings b
                LEFT JOIN customers c ON b.customer_id = c.customer_id
                LEFT JOIN rooms r ON b.room_id = r.room_id
                LEFT JOIN users u ON b.created_by = u.user_id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND b.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['customer_id'])) {
            $sql .= " AND b.customer_id = :customer_id";
            $params[':customer_id'] = $filters['customer_id'];
        }
        
        if (!empty($filters['room_id'])) {
            $sql .= " AND b.room_id = :room_id";
            $params[':room_id'] = $filters['room_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND b.check_in_date >= TO_DATE(:date_from, 'YYYY-MM-DD')";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND b.check_in_date <= TO_DATE(:date_to, 'YYYY-MM-DD')";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY b.created_at DESC";
        
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Get bookings error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get booking by ID
    public function getById($bookingId) {
        $sql = "SELECT b.*, c.full_name as customer_name, c.email, c.phone, c.address,
                r.room_number, r.room_type, r.max_occupancy,
                t.tariff_name, t.base_price,
                u.full_name as created_by_name
                FROM bookings b
                LEFT JOIN customers c ON b.customer_id = c.customer_id
                LEFT JOIN rooms r ON b.room_id = r.room_id
                LEFT JOIN tariffs t ON r.tariff_id = t.tariff_id
                LEFT JOIN users u ON b.created_by = u.user_id
                WHERE b.booking_id = :booking_id";
        $params = [':booking_id' => $bookingId];
        
        try {
            $results = $this->db->query($sql, $params);
            return count($results) > 0 ? $results[0] : null;
        } catch (Exception $e) {
            error_log("Get booking error: " . $e->getMessage());
            return null;
        }
    }
    
    // Create booking
    public function create($data) {
        $sql = "INSERT INTO bookings (booking_id, customer_id, room_id, check_in_date, 
                check_out_date, num_guests, total_amount, advance_paid, status, 
                special_requests, created_by, created_at)
                VALUES (booking_seq.NEXTVAL, :customer_id, :room_id, 
                TO_DATE(:check_in_date, 'YYYY-MM-DD'), 
                TO_DATE(:check_out_date, 'YYYY-MM-DD'),
                :num_guests, :total_amount, :advance_paid, :status, 
                :special_requests, :created_by, SYSDATE)";
        
        $params = [
            ':customer_id' => $data['customer_id'],
            ':room_id' => $data['room_id'],
            ':check_in_date' => $data['check_in_date'],
            ':check_out_date' => $data['check_out_date'],
            ':num_guests' => $data['num_guests'],
            ':total_amount' => $data['total_amount'],
            ':advance_paid' => $data['advance_paid'] ?? 0,
            ':status' => $data['status'] ?? BOOKING_PENDING,
            ':special_requests' => $data['special_requests'] ?? null,
            ':created_by' => $data['created_by']
        ];
        
        try {
            $this->db->execute($sql, $params);
            return $this->db->lastInsertId('booking_seq');
        } catch (Exception $e) {
            error_log("Create booking error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update booking
    public function update($bookingId, $data) {
        $sql = "UPDATE bookings SET 
                customer_id = :customer_id,
                room_id = :room_id,
                check_in_date = TO_DATE(:check_in_date, 'YYYY-MM-DD'),
                check_out_date = TO_DATE(:check_out_date, 'YYYY-MM-DD'),
                num_guests = :num_guests,
                total_amount = :total_amount,
                advance_paid = :advance_paid,
                status = :status,
                special_requests = :special_requests,
                updated_at = SYSDATE
                WHERE booking_id = :booking_id";
        
        $params = [
            ':booking_id' => $bookingId,
            ':customer_id' => $data['customer_id'],
            ':room_id' => $data['room_id'],
            ':check_in_date' => $data['check_in_date'],
            ':check_out_date' => $data['check_out_date'],
            ':num_guests' => $data['num_guests'],
            ':total_amount' => $data['total_amount'],
            ':advance_paid' => $data['advance_paid'],
            ':status' => $data['status'],
            ':special_requests' => $data['special_requests'] ?? null
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Update booking error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update booking status
    public function updateStatus($bookingId, $status) {
        $sql = "UPDATE bookings SET status = :status, updated_at = SYSDATE 
                WHERE booking_id = :booking_id";
        $params = [
            ':booking_id' => $bookingId,
            ':status' => $status
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Update booking status error: " . $e->getMessage());
            return false;
        }
    }
    
    // Check-in booking
    public function checkIn($bookingId, $actualCheckInDate = null) {
        $sql = "UPDATE bookings SET 
                status = :status,
                actual_check_in = TO_DATE(:actual_check_in, 'YYYY-MM-DD HH24:MI:SS'),
                updated_at = SYSDATE
                WHERE booking_id = :booking_id";
        
        $params = [
            ':booking_id' => $bookingId,
            ':status' => BOOKING_CHECKED_IN,
            ':actual_check_in' => $actualCheckInDate ?? date(DATETIME_FORMAT)
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Check-in booking error: " . $e->getMessage());
            return false;
        }
    }
    
    // Check-out booking
    public function checkOut($bookingId, $actualCheckOutDate = null) {
        $sql = "UPDATE bookings SET 
                status = :status,
                actual_check_out = TO_DATE(:actual_check_out, 'YYYY-MM-DD HH24:MI:SS'),
                updated_at = SYSDATE
                WHERE booking_id = :booking_id";
        
        $params = [
            ':booking_id' => $bookingId,
            ':status' => BOOKING_CHECKED_OUT,
            ':actual_check_out' => $actualCheckOutDate ?? date(DATETIME_FORMAT)
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Check-out booking error: " . $e->getMessage());
            return false;
        }
    }
    
    // Cancel booking
    public function cancel($bookingId, $cancellationReason = null) {
        $sql = "UPDATE bookings SET 
                status = :status,
                cancellation_reason = :cancellation_reason,
                cancelled_at = SYSDATE,
                updated_at = SYSDATE
                WHERE booking_id = :booking_id";
        
        $params = [
            ':booking_id' => $bookingId,
            ':status' => BOOKING_CANCELLED,
            ':cancellation_reason' => $cancellationReason
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Cancel booking error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get upcoming check-ins
    public function getUpcomingCheckIns($days = 1) {
        $sql = "SELECT b.*, c.full_name as customer_name, c.phone, r.room_number
                FROM bookings b
                LEFT JOIN customers c ON b.customer_id = c.customer_id
                LEFT JOIN rooms r ON b.room_id = r.room_id
                WHERE b.status = :status
                AND b.check_in_date BETWEEN SYSDATE AND SYSDATE + :days
                ORDER BY b.check_in_date";
        
        $params = [
            ':status' => BOOKING_CONFIRMED,
            ':days' => $days
        ];
        
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Get upcoming check-ins error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get upcoming check-outs
    public function getUpcomingCheckOuts($days = 1) {
        $sql = "SELECT b.*, c.full_name as customer_name, c.phone, r.room_number
                FROM bookings b
                LEFT JOIN customers c ON b.customer_id = c.customer_id
                LEFT JOIN rooms r ON b.room_id = r.room_id
                WHERE b.status = :status
                AND b.check_out_date BETWEEN SYSDATE AND SYSDATE + :days
                ORDER BY b.check_out_date";
        
        $params = [
            ':status' => BOOKING_CHECKED_IN,
            ':days' => $days
        ];
        
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Get upcoming check-outs error: " . $e->getMessage());
            return [];
        }
    }
    
    // Calculate total amount
    public function calculateAmount($roomId, $checkIn, $checkOut, $numGuests = 1) {
        // Get number of nights
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $nights = $checkInDate->diff($checkOutDate)->days;
        
        // Get room tariff
        $sql = "SELECT t.base_price FROM rooms r 
                LEFT JOIN tariffs t ON r.tariff_id = t.tariff_id 
                WHERE r.room_id = :room_id";
        $params = [':room_id' => $roomId];
        
        try {
            $result = $this->db->query($sql, $params);
            if (count($result) > 0) {
                $basePrice = $result[0]['BASE_PRICE'];
                $subtotal = $basePrice * $nights;
                $gst = $subtotal * GST_RATE;
                $serviceCharge = $subtotal * SERVICE_CHARGE;
                $total = $subtotal + $gst + $serviceCharge;
                
                return [
                    'nights' => $nights,
                    'base_price' => $basePrice,
                    'subtotal' => $subtotal,
                    'gst' => $gst,
                    'service_charge' => $serviceCharge,
                    'total' => $total
                ];
            }
            return null;
        } catch (Exception $e) {
            error_log("Calculate amount error: " . $e->getMessage());
            return null;
        }
    }
}
?>