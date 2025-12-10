<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Billing.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/AuthController.php';

class ReceptionistController {
    private $bookingModel;
    private $roomModel;
    private $customerModel;
    private $billingModel;
    private $auditModel;
    
    public function __construct() {
        AuthController::requireRole(ROLE_RECEPTIONIST);
        
        $this->bookingModel = new Booking();
        $this->roomModel = new Room();
        $this->customerModel = new Customer();
        $this->billingModel = new Billing();
        $this->auditModel = new AuditLog();
    }
    
    // Dashboard
    public function dashboard() {
        $data = [];
        
        // Today's check-ins and check-outs
        $data['todays_checkins'] = $this->bookingModel->getUpcomingCheckIns(0);
        $data['todays_checkouts'] = $this->bookingModel->getUpcomingCheckOuts(0);
        
        // Upcoming check-ins (next 7 days)
        $data['upcoming_checkins'] = $this->bookingModel->getUpcomingCheckIns(7);
        
        // Room statistics
        $data['room_stats'] = $this->roomModel->getStatistics();
        
        // Recent bookings
        $data['recent_bookings'] = array_slice($this->bookingModel->getAll([]), 0, 10);
        
        require_once __DIR__ . '/../views/receptionist/dashboard.php';
    }
    
    // Check-in page
    public function checkin() {
        $data = [];
        
        // Get bookings for today's check-in
        $data['pending_checkins'] = $this->bookingModel->getUpcomingCheckIns(0);
        
        require_once __DIR__ . '/../views/receptionist/checkin.php';
    }
    
    // Process check-in
    public function processCheckin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/receptionist/checkin');
            return;
        }
        
        $bookingId = $_POST['booking_id'] ?? null;
        
        if (!$bookingId) {
            $_SESSION['error'] = 'Booking ID is required';
            Router::redirect('/receptionist/checkin');
            return;
        }
        
        // Get booking details
        $booking = $this->bookingModel->getById($bookingId);
        
        if (!$booking) {
            $_SESSION['error'] = 'Booking not found';
            Router::redirect('/receptionist/checkin');
            return;
        }
        
        // Check if already checked in
        if ($booking['STATUS'] === BOOKING_CHECKED_IN) {
            $_SESSION['error'] = 'Booking already checked in';
            Router::redirect('/receptionist/checkin');
            return;
        }
        
        // Perform check-in
        if ($this->bookingModel->checkIn($bookingId)) {
            // Update room status to occupied
            $this->roomModel->updateStatus($booking['ROOM_ID'], ROOM_OCCUPIED);
            
            // Log the action
            $this->auditModel->log(
                AuthController::getUserId(),
                ACTION_UPDATE,
                'bookings',
                $bookingId,
                'Guest checked in'
            );
            
            $_SESSION['success'] = 'Check-in completed successfully';
        } else {
            $_SESSION['error'] = 'Failed to process check-in';
        }
        
        Router::redirect('/receptionist/checkin');
    }
    
    // Check-out page
    public function checkout() {
        $data = [];
        
        // Get bookings for today's check-out
        $data['pending_checkouts'] = $this->bookingModel->getUpcomingCheckOuts(0);
        
        require_once __DIR__ . '/../views/receptionist/checkout.php';
    }
    
    // Process check-out
    public function processCheckout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/receptionist/checkout');
            return;
        }
        
        $bookingId = $_POST['booking_id'] ?? null;
        
        if (!$bookingId) {
            $_SESSION['error'] = 'Booking ID is required';
            Router::redirect('/receptionist/checkout');
            return;
        }
        
        // Get booking details
        $booking = $this->bookingModel->getById($bookingId);
        
        if (!$booking) {
            $_SESSION['error'] = 'Booking not found';
            Router::redirect('/receptionist/checkout');
            return;
        }
        
        // Check if already checked out
        if ($booking['STATUS'] === BOOKING_CHECKED_OUT) {
            $_SESSION['error'] = 'Booking already checked out';
            Router::redirect('/receptionist/checkout');
            return;
        }
        
        // Check if billing is done
        $bill = $this->billingModel->getByBookingId($bookingId);
        if (!$bill || $bill['PAYMENT_STATUS'] !== PAYMENT_PAID) {
            $_SESSION['error'] = 'Please complete billing before check-out';
            Router::redirect('/billing/invoice?booking_id=' . $bookingId);
            return;
        }
        
        // Perform check-out
        if ($this->bookingModel->checkOut($bookingId)) {
            // Update room status to available
            $this->roomModel->updateStatus($booking['ROOM_ID'], ROOM_AVAILABLE);
            
            // Log the action
            $this->auditModel->log(
                AuthController::getUserId(),
                ACTION_UPDATE,
                'bookings',
                $bookingId,
                'Guest checked out'
            );
            
            $_SESSION['success'] = 'Check-out completed successfully';
        } else {
            $_SESSION['error'] = 'Failed to process check-out';
        }
        
        Router::redirect('/receptionist/checkout');
    }
    
    // View rooms
    public function rooms() {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'room_type' => $_GET['room_type'] ?? ''
        ];
        
        $data['rooms'] = $this->roomModel->getAll($filters);
        
        require_once __DIR__ . '/../views/receptionist/rooms.php';
    }
    
    // View invoices
    public function invoices() {
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d')
        ];
        
        $data['invoices'] = $this->billingModel->getAll($filters);
        
        require_once __DIR__ . '/../views/receptionist/invoices.php';
    }
}
?>