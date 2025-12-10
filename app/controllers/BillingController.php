<?php 
class BillingController {
    private $billingModel;
    private $bookingModel;
    private $auditModel;
    
    public function __construct() {
        AuthController::requireLogin();
        $this->billingModel = new Billing();
        $this->bookingModel = new Booking();
        $this->auditModel = new AuditLog();
    }
    
    public function invoice() {
        $bookingId = $_GET['booking_id'] ?? null;
        if (!$bookingId) {
            $_SESSION['error'] = 'Booking ID required';
            Router::redirect('/receptionist/dashboard');
            return;
        }
        
        $data['booking'] = $this->bookingModel->getById($bookingId);
        $data['bill'] = $this->billingModel->getByBookingId($bookingId);
        
        if (!$data['bill']) {
            $data['calculated_amount'] = $this->billingModel->calculateBillAmount($bookingId);
        }
        
        require_once __DIR__ . '/../views/billing/invoice.php';
    }
    
    public function generate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/receptionist/dashboard');
            return;
        }
        
        $bookingId = $_POST['booking_id'];
        $calculated = $this->billingModel->calculateBillAmount($bookingId);
        
        $billData = [
            'booking_id' => $bookingId,
            'bill_number' => $this->billingModel->generateBillNumber(),
            'room_charges' => $calculated['room_charges'],
            'additional_charges' => $_POST['additional_charges'] ?? 0,
            'tax_amount' => $calculated['tax_amount'],
            'discount' => $_POST['discount'] ?? 0,
            'total_amount' => $calculated['total_amount'] + ($_POST['additional_charges'] ?? 0) - ($_POST['discount'] ?? 0),
            'payment_status' => PAYMENT_PENDING,
            'generated_by' => AuthController::getUserId()
        ];
        
        if ($this->billingModel->create($billData)) {
            $this->auditModel->log(AuthController::getUserId(), ACTION_CREATE, 'billing', $bookingId, 'Bill generated');
            $_SESSION['success'] = 'Bill generated successfully';
        } else {
            $_SESSION['error'] = 'Failed to generate bill';
        }
        
        Router::redirect('/billing/invoice?booking_id=' . $bookingId);
    }
    
    public function processPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/receptionist/dashboard');
            return;
        }
        
        $billId = $_POST['bill_id'];
        $paymentData = [
            'payment_status' => PAYMENT_PAID,
            'payment_method' => $_POST['payment_method'],
            'transaction_id' => $_POST['transaction_id'] ?? null
        ];
        
        if ($this->billingModel->recordPayment($billId, $paymentData)) {
            $this->auditModel->log(AuthController::getUserId(), ACTION_UPDATE, 'billing', $billId, 'Payment recorded');
            $_SESSION['success'] = 'Payment recorded successfully';
        } else {
            $_SESSION['error'] = 'Failed to record payment';
        }
        
        Router::redirect('/receptionist/checkout');
    }
}

?>