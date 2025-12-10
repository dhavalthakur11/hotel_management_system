<?php
// Routing Configuration

class Router {
    private $routes = [];
    private $currentRoute = null;
    
    public function __construct() {
        $this->defineRoutes();
    }
    
    // Define all application routes
    private function defineRoutes() {
        // Auth Routes
        $this->routes['GET']['/'] = ['controller' => 'AuthController', 'method' => 'showLogin'];
        $this->routes['GET']['/login'] = ['controller' => 'AuthController', 'method' => 'showLogin'];
        $this->routes['POST']['/login'] = ['controller' => 'AuthController', 'method' => 'login'];
        $this->routes['GET']['/logout'] = ['controller' => 'AuthController', 'method' => 'logout'];
        
        // Admin Routes
        $this->routes['GET']['/admin/dashboard'] = ['controller' => 'AdminController', 'method' => 'dashboard'];
        $this->routes['GET']['/admin/rooms'] = ['controller' => 'AdminController', 'method' => 'rooms'];
        $this->routes['GET']['/admin/employees'] = ['controller' => 'AdminController', 'method' => 'employees'];
        $this->routes['GET']['/admin/customers'] = ['controller' => 'AdminController', 'method' => 'customers'];
        $this->routes['GET']['/admin/reports'] = ['controller' => 'AdminController', 'method' => 'reports'];
        $this->routes['GET']['/admin/tariffs'] = ['controller' => 'AdminController', 'method' => 'tariffs'];
        
        // Receptionist Routes
        $this->routes['GET']['/receptionist/dashboard'] = ['controller' => 'ReceptionistController', 'method' => 'dashboard'];
        $this->routes['GET']['/receptionist/checkin'] = ['controller' => 'ReceptionistController', 'method' => 'checkin'];
        $this->routes['POST']['/receptionist/checkin'] = ['controller' => 'ReceptionistController', 'method' => 'processCheckin'];
        $this->routes['GET']['/receptionist/checkout'] = ['controller' => 'ReceptionistController', 'method' => 'checkout'];
        $this->routes['POST']['/receptionist/checkout'] = ['controller' => 'ReceptionistController', 'method' => 'processCheckout'];
        $this->routes['GET']['/receptionist/rooms'] = ['controller' => 'ReceptionistController', 'method' => 'rooms'];
        $this->routes['GET']['/receptionist/invoices'] = ['controller' => 'ReceptionistController', 'method' => 'invoices'];
        
        // Customer Routes
        $this->routes['GET']['/customer/dashboard'] = ['controller' => 'CustomerController', 'method' => 'dashboard'];
        $this->routes['GET']['/customer/rooms'] = ['controller' => 'CustomerController', 'method' => 'rooms'];
        $this->routes['GET']['/customer/bookings'] = ['controller' => 'CustomerController', 'method' => 'bookings'];
        $this->routes['GET']['/customer/invoices'] = ['controller' => 'CustomerController', 'method' => 'invoices'];
        
        // Room Routes
        $this->routes['GET']['/room/inventory'] = ['controller' => 'RoomController', 'method' => 'inventory'];
        $this->routes['POST']['/room/add'] = ['controller' => 'RoomController', 'method' => 'add'];
        $this->routes['POST']['/room/update'] = ['controller' => 'RoomController', 'method' => 'update'];
        $this->routes['POST']['/room/delete'] = ['controller' => 'RoomController', 'method' => 'delete'];
        
        // Booking Routes
        $this->routes['GET']['/booking/create'] = ['controller' => 'BookingController', 'method' => 'create'];
        $this->routes['POST']['/booking/create'] = ['controller' => 'BookingController', 'method' => 'store'];
        $this->routes['GET']['/booking/view'] = ['controller' => 'BookingController', 'method' => 'view'];
        $this->routes['POST']['/booking/cancel'] = ['controller' => 'BookingController', 'method' => 'cancel'];
        
        // Billing Routes
        $this->routes['GET']['/billing/invoice'] = ['controller' => 'BillingController', 'method' => 'invoice'];
        $this->routes['POST']['/billing/generate'] = ['controller' => 'BillingController', 'method' => 'generate'];
        $this->routes['POST']['/billing/payment'] = ['controller' => 'BillingController', 'method' => 'processPayment'];
        
        // Report Routes
        $this->routes['GET']['/report/occupancy'] = ['controller' => 'ReportController', 'method' => 'occupancy'];
        $this->routes['GET']['/report/revenue'] = ['controller' => 'ReportController', 'method' => 'revenue'];
        
        // Feedback Routes
        $this->routes['GET']['/feedback'] = ['controller' => 'FeedbackController', 'method' => 'index'];
        $this->routes['POST']['/feedback/submit'] = ['controller' => 'FeedbackController', 'method' => 'submit'];
        
        // Notification Routes
        $this->routes['GET']['/notification/templates'] = ['controller' => 'NotificationController', 'method' => 'templates'];
        
        // Audit Routes
        $this->routes['GET']['/audit/logs'] = ['controller' => 'AuditLogController', 'method' => 'logs'];
    }
    
    // Route the request
    public function route() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if application is in subdirectory
        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        $requestUri = str_replace($basePath, '', $requestUri);
        
        // Remove trailing slash
        $requestUri = rtrim($requestUri, '/');
        if ($requestUri === '') {
            $requestUri = '/';
        }
        
        // Check if route exists
        if (isset($this->routes[$requestMethod][$requestUri])) {
            $this->currentRoute = $this->routes[$requestMethod][$requestUri];
            return $this->dispatch();
        }
        
        // Route not found
        $this->notFound();
    }
    
    // Dispatch the route to controller
    private function dispatch() {
        $controllerName = $this->currentRoute['controller'];
        $methodName = $this->currentRoute['method'];
        
        // Load controller file
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
        
        if (!file_exists($controllerFile)) {
            die("Controller not found: {$controllerName}");
        }
        
        require_once $controllerFile;
        
        // Instantiate controller
        if (!class_exists($controllerName)) {
            die("Controller class not found: {$controllerName}");
        }
        
        $controller = new $controllerName();
        
        // Check if method exists
        if (!method_exists($controller, $methodName)) {
            die("Method not found: {$methodName} in {$controllerName}");
        }
        
        // Call the method
        return $controller->$methodName();
    }
    
    // 404 Not Found handler
    private function notFound() {
        http_response_code(404);
        echo "404 - Page Not Found";
        exit;
    }
    
    // Redirect helper
    public static function redirect($path) {
        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        header("Location: {$basePath}{$path}");
        exit;
    }
    
    // Get current URL
    public static function url($path = '') {
        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        return $basePath . $path;
    }
}
?>