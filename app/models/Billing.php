<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class Billing {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Get all bills
    public function getAll($filters = []) {
        $sql = "SELECT bi.*, b.booking_id, c.full_name as customer_name, 
                r.room_number, u.full_name as generated_by_name
                FROM billing bi
                LEFT JOIN bookings b ON bi.booking_id = b.booking_id
                LEFT JOIN customers c ON b.customer_id = c.customer_id
                LEFT JOIN rooms r ON b.room_id = r.room_id
                LEFT JOIN users u ON bi.generated_by = u.user_id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['payment_status'])) {
            $sql .= " AND bi.payment_status = :payment_status";
            $params[':payment_status'] = $filters['payment_status'];
        }
        
        if (!empty($filters['booking_id'])) {
            $sql .= " AND bi.booking_id = :booking_id";
            $params[':booking_id'] = $filters['booking_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND bi.bill_date >= TO_DATE(:date_from, 'YYYY-MM-DD')";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND bi.bill_date <= TO_DATE(:date_to, 'YYYY-MM-DD')";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY bi.created_at DESC";
        
        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Get bills error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get bill by ID
    public function getById($billId) {
        $sql = "SELECT bi.*, b.*, c.full_name as customer_name, c.email, c.phone, c.address,
                r.room_number, r.room_type, t.base_price, u.full_name as generated_by_name
                FROM billing bi
                LEFT JOIN bookings b ON bi.booking_id = b.booking_id
                LEFT JOIN customers c ON b.customer_id = c.customer_id
                LEFT JOIN rooms r ON b.room_id = r.room_id
                LEFT JOIN tariffs t ON r.tariff_id = t.tariff_id
                LEFT JOIN users u ON bi.generated_by = u.user_id
                WHERE bi.bill_id = :bill_id";
        $params = [':bill_id' => $billId];
        
        try {
            $results = $this->db->query($sql, $params);
            return count($results) > 0 ? $results[0] : null;
        } catch (Exception $e) {
            error_log("Get bill error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get bill by booking ID
    public function getByBookingId($bookingId) {
        $sql = "SELECT * FROM billing WHERE booking_id = :booking_id";
        $params = [':booking_id' => $bookingId];
        
        try {
            $results = $this->db->query($sql, $params);
            return count($results) > 0 ? $results[0] : null;
        } catch (Exception $e) {
            error_log("Get bill by booking error: " . $e->getMessage());
            return null;
        }
    }
    
    // Create bill
    public function create($data) {
        $sql = "INSERT INTO billing (bill_id, booking_id, bill_number, bill_date,
                room_charges, additional_charges, tax_amount, discount, total_amount,
                payment_status, payment_method, payment_date, transaction_id,
                generated_by, created_at)
                VALUES (billing_seq.NEXTVAL, :booking_id, :bill_number, SYSDATE,
                :room_charges, :additional_charges, :tax_amount, :discount, :total_amount,
                :payment_status, :payment_method, :payment_date, :transaction_id,
                :generated_by, SYSDATE)";
        
        $params = [
            ':booking_id' => $data['booking_id'],
            ':bill_number' => $data['bill_number'],
            ':room_charges' => $data['room_charges'],
            ':additional_charges' => $data['additional_charges'] ?? 0,
            ':tax_amount' => $data['tax_amount'],
            ':discount' => $data['discount'] ?? 0,
            ':total_amount' => $data['total_amount'],
            ':payment_status' => $data['payment_status'] ?? PAYMENT_PENDING,
            ':payment_method' => $data['payment_method'] ?? null,
            ':payment_date' => $data['payment_date'] ?? null,
            ':transaction_id' => $data['transaction_id'] ?? null,
            ':generated_by' => $data['generated_by']
        ];
        
        try {
            $this->db->execute($sql, $params);
            return $this->db->lastInsertId('billing_seq');
        } catch (Exception $e) {
            error_log("Create bill error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update bill
    public function update($billId, $data) {
        $sql = "UPDATE billing SET 
                room_charges = :room_charges,
                additional_charges = :additional_charges,
                tax_amount = :tax_amount,
                discount = :discount,
                total_amount = :total_amount,
                payment_status = :payment_status,
                updated_at = SYSDATE
                WHERE bill_id = :bill_id";
        
        $params = [
            ':bill_id' => $billId,
            ':room_charges' => $data['room_charges'],
            ':additional_charges' => $data['additional_charges'] ?? 0,
            ':tax_amount' => $data['tax_amount'],
            ':discount' => $data['discount'] ?? 0,
            ':total_amount' => $data['total_amount'],
            ':payment_status' => $data['payment_status']
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Update bill error: " . $e->getMessage());
            return false;
        }
    }
    
    // Record payment
    public function recordPayment($billId, $data) {
        $sql = "UPDATE billing SET 
                payment_status = :payment_status,
                payment_method = :payment_method,
                payment_date = SYSDATE,
                transaction_id = :transaction_id,
                updated_at = SYSDATE
                WHERE bill_id = :bill_id";
        
        $params = [
            ':bill_id' => $billId,
            ':payment_status' => $data['payment_status'],
            ':payment_method' => $data['payment_method'],
            ':transaction_id' => $data['transaction_id'] ?? null
        ];
        
        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Record payment error: " . $e->getMessage());
            return false;
        }
    }
    
    // Generate bill number
    public function generateBillNumber() {
        $prefix = 'INV';
        $date = date('Ymd');
        
        $sql = "SELECT COUNT(*) as cnt FROM billing 
                WHERE TO_CHAR(bill_date, 'YYYYMMDD') = :date";
        $params = [':date' => $date];
        
        try {
            $result = $this->db->query($sql, $params);
            $count = $result[0]['CNT'] + 1;
            return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            error_log("Generate bill number error: " . $e->getMessage());
            return $prefix . $date . '0001';
        }
    }
    
    // Calculate bill amount
    public function calculateBillAmount($bookingId) {
        $sql = "SELECT b.*, r.room_id, t.base_price,
                (b.check_out_date - b.check_in_date) as nights
                FROM bookings b
                LEFT JOIN rooms r ON b.room_id = r.room_id
                LEFT JOIN tariffs t ON r.tariff_id = t.tariff_id
                WHERE b.booking_id = :booking_id";
        $params = [':booking_id' => $bookingId];
        
        try {
            $result = $this->db->query($sql, $params);
            if (count($result) > 0) {
                $booking = $result[0];
                $nights = $booking['NIGHTS'];
                $basePrice = $booking['BASE_PRICE'];
                
                $roomCharges = $basePrice * $nights;
                $taxAmount = $roomCharges * (GST_RATE + SERVICE_CHARGE);
                $totalAmount = $roomCharges + $taxAmount;
                
                return [
                    'room_charges' => $roomCharges,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'nights' => $nights
                ];
            }
            return null;
        } catch (Exception $e) {
            error_log("Calculate bill amount error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get revenue statistics
    public function getRevenueStats($startDate = null, $endDate = null) {
        $sql = "SELECT 
                COUNT(*) as total_bills,
                SUM(total_amount) as total_revenue,
                SUM(CASE WHEN payment_status = :paid THEN total_amount ELSE 0 END) as paid_revenue,
                SUM(CASE WHEN payment_status = :pending THEN total_amount ELSE 0 END) as pending_revenue
                FROM billing WHERE 1=1";
        
        $params = [
            ':paid' => PAYMENT_PAID,
            ':pending' => PAYMENT_PENDING
        ];
        
        if ($startDate) {
            $sql .= " AND bill_date >= TO_DATE(:start_date, 'YYYY-MM-DD')";
            $params[':start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND bill_date <= TO_DATE(:end_date, 'YYYY-MM-DD')";
            $params[':end_date'] = $endDate;
        }
        
        try {
            $result = $this->db->query($sql, $params);
            return $result[0];
        } catch (Exception $e) {
            error_log("Get revenue stats error: " . $e->getMessage());
            return null;
        }
    }
}
?>