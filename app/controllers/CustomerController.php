<?php
// controllers/CustomerController.php
require_once __DIR__ . '/AuthController.php';

class CustomerController {
    private $bookingModel;
    private $roomModel;
    private $customerModel;
    private $billingModel;
    
    public function __construct() {
        AuthController::requireRole(ROLE_CUSTOMER);
        $this->bookingModel = new Booking();
        $this->roomModel = new Room();
        $this->customerModel = new Customer();
        $this->billingModel = new Billing();
    }
    
    public function dashboard() {
        $customer = $this->customerModel->getByUserId(AuthController::getUserId());
        $data['bookings'] = $this->bookingModel->getAll(['customer_id' => $customer['CUSTOMER_ID']]);
        $data['upcoming'] = array_filter($data['bookings'], function($b) {
            return strtotime($b['CHECK_IN_DATE']) >= time();
        });
        require_once __DIR__ . '/../views/customer/dashboard.php';
    }
    
    public function rooms() {
        $checkIn = $_GET['check_in'] ?? date('Y-m-d', strtotime('+1 day'));
        $checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+2 days'));
        $data['available_rooms'] = $this->roomModel->getAvailableRooms($checkIn, $checkOut);
        $data['check_in'] = $checkIn;
        $data['check_out'] = $checkOut;
        require_once __DIR__ . '/../views/customer/rooms.php';
    }
    
    public function bookings() {
        $customer = $this->customerModel->getByUserId(AuthController::getUserId());
        $data['bookings'] = $this->bookingModel->getAll(['customer_id' => $customer['CUSTOMER_ID']]);
        require_once __DIR__ . '/../views/customer/bookings.php';
    }
    
    public function invoices() {
        $customer = $this->customerModel->getByUserId(AuthController::getUserId());
        $bookings = $this->bookingModel->getAll(['customer_id' => $customer['CUSTOMER_ID']]);
        $data['invoices'] = [];
        foreach ($bookings as $booking) {
            $bill = $this->billingModel->getByBookingId($booking['BOOKING_ID']);
            if ($bill) $data['invoices'][] = $bill;
        }
        require_once __DIR__ . '/../views/customer/invoices.php';
    }
}
?>