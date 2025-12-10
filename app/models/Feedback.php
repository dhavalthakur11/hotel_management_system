<?php
class Feedback {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        $sql = "SELECT f.*, c.full_name as customer_name, b.booking_id 
                FROM feedback f
                LEFT JOIN customers c ON f.customer_id = c.customer_id
                LEFT JOIN bookings b ON f.booking_id = b.booking_id
                ORDER BY f.created_at DESC";
        try {
            return $this->db->query($sql);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function create($data) {
        $sql = "INSERT INTO feedback (feedback_id, booking_id, customer_id, rating,
                comments, created_at) VALUES (feedback_seq.NEXTVAL, :booking_id,
                :customer_id, :rating, :comments, SYSDATE)";
        try {
            return $this->db->execute($sql, [
                ':booking_id' => $data['booking_id'],
                ':customer_id' => $data['customer_id'],
                ':rating' => $data['rating'],
                ':comments' => $data['comments']
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>