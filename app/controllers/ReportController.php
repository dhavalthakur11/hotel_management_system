<?php 
class ReportController {
    private $bookingModel;
    private $billingModel;
    private $roomModel;
    
    public function __construct() {
        AuthController::requireRole(ROLE_ADMIN);
        $this->bookingModel = new Booking();
        $this->billingModel = new Billing();
        $this->roomModel = new Room();
    }
    
    public function occupancy() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $data['bookings'] = $this->bookingModel->getAll([
            'date_from' => $startDate,
            'date_to' => $endDate
        ]);
        $data['room_stats'] = $this->roomModel->getStatistics();
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        
        require_once __DIR__ . '/../views/report/occupancy.php';
    }
    
    public function revenue() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $data['revenue_stats'] = $this->billingModel->getRevenueStats($startDate, $endDate);
        $data['bills'] = $this->billingModel->getAll([
            'date_from' => $startDate,
            'date_to' => $endDate
        ]);
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        
        require_once __DIR__ . '/../views/report/revenue.php';
    }
}
?>