<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/AuthController.php';

class BookingController {
    private $bookingModel;
    private $roomModel;
    private $customerModel;
    private $notificationModel;
    private $auditModel;
    
    public function __construct() {
        AuthController::requireLogin();
        
        $this->bookingModel = new Booking();
        $this->roomModel = new Room();
        $this->customerModel = new Customer();
        $this->notificationModel = new Notification();
        $this->auditModel = new AuditLog();
    }
    
    // Show booking creation form
    public function create() {
        $data = [];
        
        // Get available rooms if dates are provided
        if (isset($_GET['check_in']) && isset($_GET['check_out'])) {
            $data['check_in'] = $_GET['check_in'];
            $data['check_out'] = $_GET['check_out'];
            $data['room_type'] = $_GET['room_type'] ?? null;
            
            $data['available_rooms'] = $this->roomModel->getAvailableRooms(
                $data['check_in'],
                $data['check_out'],
                $data['room_type']
            );
        }
        
        // Get all customers for receptionist/admin
        $userRole = AuthController::getUserRole();
        if ($userRole === ROLE_ADMIN || $userRole === ROLE_RECEPTIONIST) {
            $data['customers'] = $this->customerModel->getAll();
        }
        
        require_once __DIR__ . '/../views/booking/create.php';
    }
    
    // Store new booking
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/booking/create');
            return;
        }
        
        // Validate input
        $errors = $this->validateBookingData($_POST);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            Router::redirect('/booking/create');
            return;
        }
        
        // Get customer ID
        $customerId = $this->getOrCreateCustomer($_POST);
        if (!$customerId) {
            $_SESSION['error'] = 'Failed to create customer record';
            Router::redirect('/booking/create');
            return;
        }
        
        // Calculate booking amount
        $amountDetails = $this->bookingModel->calculateAmount(
            $_POST['room_id'],
            $_POST['check_in_date'],
            $_POST['check_out_date'],
            $_POST['num_guests']
        );
        
        if (!$amountDetails) {
            $_SESSION['error'] = 'Failed to calculate booking amount';
            Router::redirect('/booking/create');
            return;
        }
        
        // Create booking
        $bookingData = [
            'customer_id' => $customerId,
            'room_id' => $_POST['room_id'],
            'check_in_date' => $_POST['check_in_date'],
            'check_out_date' => $_POST['check_out_date'],
            'num_guests' => $_POST['num_guests'],
            'total_amount' => $amountDetails['total'],
            'advance_paid' => $_POST['advance_paid'] ?? 0,
            'status' => BOOKING_CONFIRMED,
            'special_requests' => $_POST['special_requests'] ?? null,
            'created_by' => AuthController::getUserId()
        ];
        
        $bookingId = $this->bookingModel->create($bookingData);
        
        if ($bookingId) {
            // Update room status to reserved
            $this->roomModel->updateStatus($_POST['room_id'], ROOM_RESERVED);
            
            // Log the action
            $this->auditModel->log(
                AuthController::getUserId(),
                ACTION_CREATE,
                'bookings',
                $bookingId,
                'Booking created successfully'
            );
            
            // Send confirmation notification
            $this->sendBookingConfirmation($bookingId, $customerId);
            
            $_SESSION['success'] = MSG_BOOKING_SUCCESS;
            $_SESSION['booking_id'] = $bookingId;
            Router::redirect('/booking/view?id=' . $bookingId);
        } else {
            $_SESSION['error'] = 'Failed to create booking';
            Router::redirect('/booking/create');
        }
    }
    
    // View booking details
    public function view() {
        $bookingId = $_GET['id'] ?? null;
        
        if (!$bookingId) {
            $_SESSION['error'] = 'Booking ID is required';
            Router::redirect('/customer/dashboard');
            return;
        }
        
        $data['booking'] = $this->bookingModel->getById($bookingId);
        
        if (!$data['booking']) {
            $_SESSION['error'] = 'Booking not found';
            Router::redirect('/customer/dashboard');
            return;
        }
        
        // Check authorization
        $userRole = AuthController::getUserRole();
        if ($userRole === ROLE_CUSTOMER) {
            $customer = $this->customerModel->getByUserId(AuthController::getUserId());
            if ($customer['CUSTOMER_ID'] != $data['booking']['CUSTOMER_ID']) {
                $_SESSION['error'] = ERR_UNAUTHORIZED;
                Router::redirect('/customer/dashboard');
                return;
            }
        }
        
        require_once __DIR__ . '/../views/booking/view.php';
    }
    
    // Cancel booking
    public function cancel() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/customer/bookings');
            return;
        }
        
        $bookingId = $_POST['booking_id'] ?? null;
        $cancellationReason = $_POST['cancellation_reason'] ?? null;
        
        if (!$bookingId) {
            $_SESSION['error'] = 'Booking ID is required';
            Router::redirect('/customer/bookings');
            return;
        }
        
        // Get booking details
        $booking = $this->bookingModel->getById($bookingId);
        
        if (!$booking) {
            $_SESSION['error'] = 'Booking not found';
            Router::redirect('/customer/bookings');
            return;
        }
        
        // Check authorization
        $userRole = AuthController::getUserRole();
        if ($userRole === ROLE_CUSTOMER) {
            $customer = $this->customerModel->getByUserId(AuthController::getUserId());
            if ($customer['CUSTOMER_ID'] != $booking['CUSTOMER_ID']) {
                $_SESSION['error'] = ERR_UNAUTHORIZED;
                Router::redirect('/customer/bookings');
                return;
            }
        }
        
        // Check if booking can be cancelled
        if ($booking['STATUS'] === BOOKING_CHECKED_IN || $booking['STATUS'] === BOOKING_CHECKED_OUT) {
            $_SESSION['error'] = 'Cannot cancel booking that is already checked in or checked out';
            Router::redirect('/booking/view?id=' . $bookingId);
            return;
        }
        
        // Cancel booking
        if ($this->bookingModel->cancel($bookingId, $cancellationReason)) {
            // Update room status back to available
            $this->roomModel->updateStatus($booking['ROOM_ID'], ROOM_AVAILABLE);
            
            // Log the action
            $this->auditModel->log(
                AuthController::getUserId(),
                ACTION_UPDATE,
                'bookings',
                $bookingId,
                'Booking cancelled'
            );
            
            $_SESSION['success'] = 'Booking cancelled successfully';
        } else {
            $_SESSION['error'] = 'Failed to cancel booking';
        }
        
        Router::redirect('/booking/view?id=' . $bookingId);
    }
    
    // Validate booking data
    private function validateBookingData($data) {
        $errors = [];
        
        if (empty($data['room_id'])) {
            $errors[] = 'Please select a room';
        }
        
        if (empty($data['check_in_date'])) {
            $errors[] = 'Check-in date is required';
        }
        
        if (empty($data['check_out_date'])) {
            $errors[] = 'Check-out date is required';
        }
        
        if (!empty($data['check_in_date']) && !empty($data['check_out_date'])) {
            $checkIn = new DateTime($data['check_in_date']);
            $checkOut = new DateTime($data['check_out_date']);
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            
            if ($checkIn < $today) {
                $errors[] = 'Check-in date cannot be in the past';
            }
            
            if ($checkOut <= $checkIn) {
                $errors[] = 'Check-out date must be after check-in date';
            }
        }
        
        if (empty($data['num_guests']) || $data['num_guests'] < 1) {
            $errors[] = 'Number of guests must be at least 1';
        }
        
        // Check if room is available
        if (!empty($data['room_id']) && !empty($data['check_in_date']) && !empty($data['check_out_date'])) {
            $availableRooms = $this->roomModel->getAvailableRooms(
                $data['check_in_date'],
                $data['check_out_date']
            );
            
            $roomAvailable = false;
            foreach ($availableRooms as $room) {
                if ($room['ROOM_ID'] == $data['room_id']) {
                    $roomAvailable = true;
                    break;
                }
            }
            
            if (!$roomAvailable) {
                $errors[] = ERR_ROOM_NOT_AVAILABLE;
            }
        }
        
        return $errors;
    }
    
    // Get or create customer
    private function getOrCreateCustomer($data) {
        $userRole = AuthController::getUserRole();
        
        // If admin/receptionist, use selected customer
        if ($userRole === ROLE_ADMIN || $userRole === ROLE_RECEPTIONIST) {
            if (!empty($data['customer_id'])) {
                return $data['customer_id'];
            }
            
            // Create new customer
            $customerData = [
                'full_name' => $data['customer_name'],
                'email' => $data['customer_email'],
                'phone' => $data['customer_phone'],
                'address' => $data['customer_address'] ?? null,
                'id_proof_type' => $data['id_proof_type'] ?? null,
                'id_proof_number' => $data['id_proof_number'] ?? null
            ];
            
            return $this->customerModel->create($customerData);
        }
        
        // If customer, get their customer ID
        if ($userRole === ROLE_CUSTOMER) {
            $customer = $this->customerModel->getByUserId(AuthController::getUserId());
            return $customer ? $customer['CUSTOMER_ID'] : null;
        }
        
        return null;
    }
    
    // Send booking confirmation
    private function sendBookingConfirmation($bookingId, $customerId) {
        $booking = $this->bookingModel->getById($bookingId);
        $customer = $this->customerModel->getById($customerId);
        
        if ($booking && $customer) {
            $message = "Dear {$customer['FULL_NAME']},\n\n";
            $message .= "Your booking has been confirmed.\n\n";
            $message .= "Booking ID: {$bookingId}\n";
            $message .= "Room: {$booking['ROOM_NUMBER']}\n";
            $message .= "Check-in: {$booking['CHECK_IN_DATE']}\n";
            $message .= "Check-out: {$booking['CHECK_OUT_DATE']}\n";
            $message .= "Total Amount: ₹{$booking['TOTAL_AMOUNT']}\n\n";
            $message .= "Thank you for choosing us!";
            
            $this->notificationModel->send([
                'user_id' => $customer['USER_ID'],
                'type' => NOTIF_BOOKING_CONFIRMATION,
                'subject' => 'Booking Confirmation',
                'message' => $message
            ]);
        }
    }
}
?>