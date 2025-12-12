<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/routing.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/AuditLog.php';

class AuthController {
    private $userModel;
    private $auditModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->auditModel = new AuditLog();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Show login page
    public function showLogin() {
        // If already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirectToDashboard();
        }
        
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    // Process login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/login');
            return;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        // Validate input
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Please enter username and password';
            Router::redirect('/login');
            return;
        }
        
        // Check for too many failed attempts (basic rate limiting)
        $this->checkLoginAttempts();
        
        // Authenticate user
        $user = $this->userModel->authenticate($username, $password);
        
        if ($user) {
            // Clear failed login attempts
            unset($_SESSION['failed_login_attempts']);
            unset($_SESSION['last_failed_login']);
            
            // Set session variables
            $_SESSION['user_id'] = $user['USER_ID'];
            $_SESSION['username'] = $user['USERNAME'];
            $_SESSION['role'] = $user['ROLE'];
            $_SESSION['full_name'] = $user['FULL_NAME'];
            $_SESSION['email'] = $user['EMAIL'];
            $_SESSION['last_activity'] = time();
            $_SESSION['login_time'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Log the login action
            $this->auditModel->log(
                $user['USER_ID'],
                ACTION_LOGIN,
                'users',
                $user['USER_ID'],
                'User logged in successfully'
            );
            
            // Redirect to appropriate dashboard
            $_SESSION['success'] = MSG_LOGIN_SUCCESS;
            $this->redirectToDashboard();
        } else {
            // Track failed login attempt
            $this->trackFailedLogin();
            
            $_SESSION['error'] = ERR_INVALID_CREDENTIALS;
            Router::redirect('/login');
        }
    }
    
    // Logout
    public function logout() {
        if ($this->isLoggedIn()) {
            // Log the logout action
            $this->auditModel->log(
                $_SESSION['user_id'],
                ACTION_LOGOUT,
                'users',
                $_SESSION['user_id'],
                'User logged out'
            );
        }
        
        // Clear all session data
        $_SESSION = array();
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        
        // Start new session for flash message
        session_start();
        $_SESSION['success'] = MSG_LOGOUT_SUCCESS;
        
        Router::redirect('/login');
    }
    
    // Check if user is logged in
    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            $elapsed = time() - $_SESSION['last_activity'];
            if ($elapsed > SESSION_TIMEOUT) {
                // Session expired
                session_unset();
                session_destroy();
                return false;
            }
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    // Check if user has specific role
    public static function hasRole($role) {
        if (!self::isLoggedIn()) {
            return false;
        }
        return $_SESSION['role'] === $role;
    }
    
    // Require login (use in controllers)
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            $_SESSION['error'] = ERR_SESSION_EXPIRED;
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            Router::redirect('/login');
            exit;
        }
    }
    
    // Require specific role
    public static function requireRole($role) {
        self::requireLogin();
        
        if (!self::hasRole($role)) {
            $_SESSION['error'] = ERR_UNAUTHORIZED;
            Router::redirect('/login');
            exit;
        }
    }
    
    // Redirect to appropriate dashboard based on role
    private function redirectToDashboard() {
        $role = $_SESSION['role'] ?? '';
        
        // Check if there's a redirect URL stored
        if (isset($_SESSION['redirect_after_login'])) {
            $redirectUrl = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            Router::redirect($redirectUrl);
            return;
        }
        
        // Default redirects based on role
        switch ($role) {
            case ROLE_ADMIN:
                Router::redirect('/admin/dashboard');
                break;
            case ROLE_RECEPTIONIST:
                Router::redirect('/receptionist/dashboard');
                break;
            case ROLE_CUSTOMER:
                Router::redirect('/customer/dashboard');
                break;
            default:
                Router::redirect('/login');
        }
    }
    
    // Get current user ID
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    // Get current user role
    public static function getUserRole() {
        return $_SESSION['role'] ?? null;
    }
    
    // Get current user name
    public static function getUserName() {
        return $_SESSION['full_name'] ?? '';
    }
    
    // Get current user email
    public static function getUserEmail() {
        return $_SESSION['email'] ?? '';
    }
    
    // Get current username
    // public static function getUsername() {
    //     return $_SESSION['username'] ?? '';
    // }
    
    // Track failed login attempts (basic rate limiting)
    private function trackFailedLogin() {
        if (!isset($_SESSION['failed_login_attempts'])) {
            $_SESSION['failed_login_attempts'] = 0;
        }
        
        $_SESSION['failed_login_attempts']++;
        $_SESSION['last_failed_login'] = time();
    }
    
    // Check login attempts
    private function checkLoginAttempts() {
        if (isset($_SESSION['failed_login_attempts']) && $_SESSION['failed_login_attempts'] >= 5) {
            $timeSinceLastFailed = time() - ($_SESSION['last_failed_login'] ?? 0);
            
            // Lock account for 15 minutes after 5 failed attempts
            if ($timeSinceLastFailed < 900) { // 15 minutes
                $remainingTime = ceil((900 - $timeSinceLastFailed) / 60);
                $_SESSION['error'] = "Too many failed login attempts. Please try again in {$remainingTime} minutes.";
                Router::redirect('/login');
                exit;
            } else {
                // Reset attempts after lockout period
                unset($_SESSION['failed_login_attempts']);
                unset($_SESSION['last_failed_login']);
            }
        }
    }
    
    // Change password
    public function changePassword() {
        self::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/auth/change_password.php';
            return;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        $errors = [];
        
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required';
        }
        
        if (empty($newPassword)) {
            $errors[] = 'New password is required';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            require_once __DIR__ . '/../views/auth/change_password.php';
            return;
        }
        
        // Verify current password
        $user = $this->userModel->getById(self::getUserId());
        
        if (!password_verify($currentPassword, $user['PASSWORD'])) {
            $_SESSION['error'] = 'Current password is incorrect';
            require_once __DIR__ . '/../views/auth/change_password.php';
            return;
        }
        
        // Update password
        if ($this->userModel->updatePassword(self::getUserId(), $newPassword)) {
            // Log the action
            $this->auditModel->log(
                self::getUserId(),
                ACTION_UPDATE,
                'users',
                self::getUserId(),
                'User changed their password'
            );
            
            $_SESSION['success'] = 'Password changed successfully';
            $this->redirectToDashboard();
        } else {
            $_SESSION['error'] = 'Failed to change password';
            require_once __DIR__ . '/../views/auth/change_password.php';
        }
    }
    
    // Forgot password (placeholder for future implementation)
    public function forgotPassword() {
        // This would typically send a password reset email
        // For now, just show a message
        $_SESSION['info'] = 'Password reset functionality will be implemented soon. Please contact administrator.';
        Router::redirect('/login');
    }
    
    // Check if current user can access resource
    public static function canAccess($resource, $action = 'view') {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        $role = self::getUserRole();
        
        // Admin can access everything
        if ($role === ROLE_ADMIN) {
            return true;
        }
        
        // Define permissions for each role
        $permissions = [
            ROLE_RECEPTIONIST => [
                'bookings' => ['view', 'create', 'update'],
                'rooms' => ['view'],
                'customers' => ['view', 'create'],
                'billing' => ['view', 'create', 'update'],
                'checkin' => ['create'],
                'checkout' => ['create']
            ],
            ROLE_CUSTOMER => [
                'bookings' => ['view', 'create'],
                'rooms' => ['view'],
                'invoices' => ['view'],
                'feedback' => ['create']
            ]
        ];
        
        if (!isset($permissions[$role][$resource])) {
            return false;
        }
        
        return in_array($action, $permissions[$role][$resource]);
    }
    
    // Get session info for debugging
    public static function getSessionInfo() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'user_id' => self::getUserId(),
            'username' => self::getUsername(),
            'role' => self::getUserRole(),
            'full_name' => self::getUserName(),
            'email' => self::getUserEmail(),
            'login_time' => $_SESSION['login_time'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'ip_address' => $_SESSION['ip_address'] ?? null
        ];
    }
}
?>