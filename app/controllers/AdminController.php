<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Employee.php';
require_once __DIR__ . '/../models/Billing.php';
require_once __DIR__ . '/../models/Tariff.php';
require_once __DIR__ . '/AuthController.php';

class AdminController {
    private $userModel;
    private $roomModel;
    private $bookingModel;
    private $customerModel;
    private $employeeModel;
    private $billingModel;
    private $tariffModel;
    
    public function __construct() {
        AuthController::requireRole(ROLE_ADMIN);
        
        $this->userModel = new User();
        $this->roomModel = new Room();
        $this->bookingModel = new Booking();
        $this->customerModel = new Customer();
        $this->employeeModel = new Employee();
        $this->billingModel = new Billing();
        $this->tariffModel = new Tariff();
    }
    
    // Dashboard
    public function dashboard() {
        $data = [];
        
        // Get statistics
        $data['room_stats'] = $this->roomModel->getStatistics();
        $data['revenue_stats'] = $this->billingModel->getRevenueStats();
        
        // Get today's bookings
        $data['todays_checkins'] = $this->bookingModel->getUpcomingCheckIns(0);
        $data['todays_checkouts'] = $this->bookingModel->getUpcomingCheckOuts(0);
        
        // Get recent bookings
        $data['recent_bookings'] = $this->bookingModel->getAll([]);
        $data['recent_bookings'] = array_slice($data['recent_bookings'], 0, 10);
        
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
    
    // Rooms Management
    public function rooms() {
        $data = [];
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'add':
                        $this->addRoom($_POST);
                        break;
                    case 'update':
                        $this->updateRoom($_POST);
                        break;
                    case 'delete':
                        $this->deleteRoom($_POST['room_id']);
                        break;
                }
            }
        }
        
        // Get filters
        $filters = [
            'status' => $_GET['status'] ?? '',
            'room_type' => $_GET['room_type'] ?? '',
            'floor' => $_GET['floor'] ?? ''
        ];
        
        $data['rooms'] = $this->roomModel->getAll($filters);
        $data['tariffs'] = $this->tariffModel->getAll();
        
        require_once __DIR__ . '/../views/admin/rooms.php';
    }
    
    private function addRoom($postData) {
        $roomData = [
            'room_number' => $postData['room_number'],
            'room_type' => $postData['room_type'],
            'floor_number' => $postData['floor_number'],
            'max_occupancy' => $postData['max_occupancy'],
            'tariff_id' => $postData['tariff_id'],
            'status' => $postData['status'],
            'amenities' => $postData['amenities'] ?? null,
            'description' => $postData['description'] ?? null
        ];
        
        if ($this->roomModel->create($roomData)) {
            $_SESSION['success'] = 'Room added successfully';
        } else {
            $_SESSION['error'] = 'Failed to add room';
        }
        
        Router::redirect('/admin/rooms');
    }
    
    private function updateRoom($postData) {
        $roomId = $postData['room_id'];
        $roomData = [
            'room_number' => $postData['room_number'],
            'room_type' => $postData['room_type'],
            'floor_number' => $postData['floor_number'],
            'max_occupancy' => $postData['max_occupancy'],
            'tariff_id' => $postData['tariff_id'],
            'status' => $postData['status'],
            'amenities' => $postData['amenities'] ?? null,
            'description' => $postData['description'] ?? null
        ];
        
        if ($this->roomModel->update($roomId, $roomData)) {
            $_SESSION['success'] = 'Room updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update room';
        }
        
        Router::redirect('/admin/rooms');
    }
    
    private function deleteRoom($roomId) {
        if ($this->roomModel->delete($roomId)) {
            $_SESSION['success'] = 'Room deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete room';
        }
        
        Router::redirect('/admin/rooms');
    }
    
    // Employees Management
    public function employees() {
        $data = [];
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'add':
                        $this->addEmployee($_POST);
                        break;
                    case 'update':
                        $this->updateEmployee($_POST);
                        break;
                    case 'delete':
                        $this->deleteEmployee($_POST['employee_id']);
                        break;
                }
            }
        }
        
        $data['employees'] = $this->employeeModel->getAll();
        
        require_once __DIR__ . '/../views/admin/employees.php';
    }
    
    private function addEmployee($postData) {
        // First create user account
        $userData = [
            'username' => $postData['username'],
            'password' => $postData['password'],
            'email' => $postData['email'],
            'full_name' => $postData['full_name'],
            'phone' => $postData['phone'],
            'role' => $postData['role']
        ];
        
        $userId = $this->userModel->create($userData);
        
        if ($userId) {
            // Then create employee record
            $employeeData = [
                'user_id' => $userId,
                'full_name' => $postData['full_name'],
                'phone' => $postData['phone'],
                'department' => $postData['department'],
                'designation' => $postData['designation'],
                'salary' => $postData['salary'],
                'joining_date' => $postData['joining_date'],
                'shift_timing' => $postData['shift_timing'] ?? null
            ];
            
            if ($this->employeeModel->create($employeeData)) {
                $_SESSION['success'] = 'Employee added successfully';
            } else {
                $_SESSION['error'] = 'Failed to add employee';
            }
        } else {
            $_SESSION['error'] = 'Failed to create user account';
        }
        
        Router::redirect('/admin/employees');
    }
    
    private function updateEmployee($postData) {
        $employeeId = $postData['employee_id'];
        $employeeData = [
            'full_name' => $postData['full_name'],
            'phone' => $postData['phone'],
            'department' => $postData['department'],
            'designation' => $postData['designation'],
            'salary' => $postData['salary'],
            'shift_timing' => $postData['shift_timing'] ?? null
        ];
        
        if ($this->employeeModel->update($employeeId, $employeeData)) {
            $_SESSION['success'] = 'Employee updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update employee';
        }
        
        Router::redirect('/admin/employees');
    }
    
    private function deleteEmployee($employeeId) {
        if ($this->employeeModel->delete($employeeId)) {
            $_SESSION['success'] = 'Employee deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete employee';
        }
        
        Router::redirect('/admin/employees');
    }
    
    // Customers Management
    public function customers() {
        $search = $_GET['search'] ?? '';
        $data['customers'] = $this->customerModel->getAll($search);
        
        require_once __DIR__ . '/../views/admin/customers.php';
    }
    
    // Tariffs Management
    public function tariffs() {
        $data = [];
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'add':
                        $this->addTariff($_POST);
                        break;
                    case 'update':
                        $this->updateTariff($_POST);
                        break;
                }
            }
        }
        
        $data['tariffs'] = $this->tariffModel->getAll();
        
        require_once __DIR__ . '/../views/admin/tariffs.php';
    }
    
    private function addTariff($postData) {
        $tariffData = [
            'tariff_name' => $postData['tariff_name'],
            'base_price' => $postData['base_price'],
            'description' => $postData['description'] ?? null
        ];
        
        if ($this->tariffModel->create($tariffData)) {
            $_SESSION['success'] = 'Tariff added successfully';
        } else {
            $_SESSION['error'] = 'Failed to add tariff';
        }
        
        Router::redirect('/admin/tariffs');
    }
    
    private function updateTariff($postData) {
        $tariffId = $postData['tariff_id'];
        $tariffData = [
            'tariff_name' => $postData['tariff_name'],
            'base_price' => $postData['base_price'],
            'description' => $postData['description'] ?? null
        ];
        
        if ($this->tariffModel->update($tariffId, $tariffData)) {
            $_SESSION['success'] = 'Tariff updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update tariff';
        }
        
        Router::redirect('/admin/tariffs');
    }
    
    // Reports
    public function reports() {
        require_once __DIR__ . '/../views/admin/reports.php';
    }
}
?>