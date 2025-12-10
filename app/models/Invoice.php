<?php
class Invoice {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getByBillingId($billingId) {
        $sql = "SELECT i.*, bi.*, b.*, c.full_name as customer_name, r.room_number
                FROM invoices i
                LEFT JOIN billing bi ON i.billing_id = bi.bill_id
                LEFT JOIN bookings b ON bi.booking_id = b.booking_id
                LEFT JOIN customers c ON b.customer_id = c.customer_id
                LEFT JOIN rooms r ON b.room_id = r.room_id
                WHERE i.billing_id = :billing_id";
        try {
            $results = $this->db->query($sql, [':billing_id' => $billingId]);
            return count($results) > 0 ? $results[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function create($billingId, $invoiceNumber) {
        $sql = "INSERT INTO invoices (invoice_id, billing_id, invoice_number, 
                generated_date, created_at) VALUES (invoice_seq.NEXTVAL, :billing_id,
                :invoice_number, SYSDATE, SYSDATE)";
        try {
            return $this->db->execute($sql, [
                ':billing_id' => $billingId,
                ':invoice_number' => $invoiceNumber
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>