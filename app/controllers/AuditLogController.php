<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/AuthController.php';

class AuditLogController {
    private $auditModel;
    private $userModel;
    
    public function __construct() {
        // Only admin can access audit logs
        AuthController::requireRole(ROLE_ADMIN);
        
        $this->auditModel = new AuditLog();
        $this->userModel = new User();
    }
    
    // View audit logs
    public function logs() {
        $data = [];
        
        // Get filter parameters
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'action' => $_GET['action'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'table_name' => $_GET['table_name'] ?? ''
        ];
        
        // Get all logs with filters
        $data['logs'] = $this->auditModel->getAll($filters);
        
        // Get all users for filter dropdown
        $data['users'] = $this->userModel->getAll();
        
        // Get available actions for filter
        $data['actions'] = [
            ACTION_CREATE,
            ACTION_UPDATE,
            ACTION_DELETE,
            ACTION_LOGIN,
            ACTION_LOGOUT,
            ACTION_VIEW
        ];
        
        // Get available tables for filter
        $data['tables'] = [
            'users',
            'customers',
            'employees',
            'rooms',
            'bookings',
            'billing',
            'tariffs',
            'feedback'
        ];
        
        // Pagination settings
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = RECORDS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        // Get total count for pagination
        $data['total_logs'] = count($data['logs']);
        $data['total_pages'] = ceil($data['total_logs'] / $perPage);
        $data['current_page'] = $page;
        
        // Slice logs for current page
        $data['logs'] = array_slice($data['logs'], $offset, $perPage);
        
        // Statistics
        $data['stats'] = $this->getLogStatistics($filters);
        
        // Pass filters to view
        $data['filters'] = $filters;
        
        require_once __DIR__ . '/../views/audit/logs.php';
    }
    
    // Get log statistics
    private function getLogStatistics($filters = []) {
        $allLogs = $this->auditModel->getAll($filters);
        
        $stats = [
            'total' => count($allLogs),
            'by_action' => [],
            'by_user' => [],
            'by_table' => [],
            'today' => 0,
            'this_week' => 0,
            'this_month' => 0
        ];
        
        $today = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        $monthAgo = date('Y-m-d', strtotime('-30 days'));
        
        foreach ($allLogs as $log) {
            // Count by action
            $action = $log['ACTION'];
            if (!isset($stats['by_action'][$action])) {
                $stats['by_action'][$action] = 0;
            }
            $stats['by_action'][$action]++;
            
            // Count by user
            $userName = $log['FULL_NAME'] ?? 'Unknown';
            if (!isset($stats['by_user'][$userName])) {
                $stats['by_user'][$userName] = 0;
            }
            $stats['by_user'][$userName]++;
            
            // Count by table
            $table = $log['TABLE_NAME'];
            if ($table) {
                if (!isset($stats['by_table'][$table])) {
                    $stats['by_table'][$table] = 0;
                }
                $stats['by_table'][$table]++;
            }
            
            // Count by date
            $logDate = date('Y-m-d', strtotime($log['CREATED_AT']));
            if ($logDate === $today) {
                $stats['today']++;
            }
            if ($logDate >= $weekAgo) {
                $stats['this_week']++;
            }
            if ($logDate >= $monthAgo) {
                $stats['this_month']++;
            }
        }
        
        // Sort statistics
        arsort($stats['by_action']);
        arsort($stats['by_user']);
        arsort($stats['by_table']);
        
        return $stats;
    }
    
    // Export logs to CSV
    public function exportCsv() {
        // Get filters
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'action' => $_GET['action'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'table_name' => $_GET['table_name'] ?? ''
        ];
        
        // Get all logs
        $logs = $this->auditModel->getAll($filters);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d_His') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Write CSV header
        fputcsv($output, [
            'Audit ID',
            'User',
            'Action',
            'Table Name',
            'Record ID',
            'Description',
            'IP Address',
            'Date/Time'
        ]);
        
        // Write data rows
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['AUDIT_ID'],
                $log['FULL_NAME'] ?? 'Unknown',
                $log['ACTION'],
                $log['TABLE_NAME'],
                $log['RECORD_ID'],
                $log['DESCRIPTION'],
                $log['IP_ADDRESS'],
                date('Y-m-d H:i:s', strtotime($log['CREATED_AT']))
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    // View single log detail
    public function viewDetail() {
        $auditId = $_GET['id'] ?? null;
        
        if (!$auditId) {
            $_SESSION['error'] = 'Audit ID is required';
            Router::redirect('/audit/logs');
            return;
        }
        
        // Get log details
        $sql = "SELECT a.*, u.username, u.full_name, u.email 
                FROM audit_logs a
                LEFT JOIN users u ON a.user_id = u.user_id
                WHERE a.audit_id = :audit_id";
        
        try {
            $db = Database::getInstance();
            $result = $db->query($sql, [':audit_id' => $auditId]);
            
            if (empty($result)) {
                $_SESSION['error'] = 'Audit log not found';
                Router::redirect('/audit/logs');
                return;
            }
            
            $data['log'] = $result[0];
            require_once __DIR__ . '/../views/audit/detail.php';
            
        } catch (Exception $e) {
            error_log("View audit detail error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to load audit log details';
            Router::redirect('/audit/logs');
        }
    }
    
    // Clear old logs (older than specified days)
    public function clearOldLogs() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/audit/logs');
            return;
        }
        
        $days = $_POST['days'] ?? 90;
        
        if ($days < 30) {
            $_SESSION['error'] = 'Cannot delete logs less than 30 days old';
            Router::redirect('/audit/logs');
            return;
        }
        
        $sql = "DELETE FROM audit_logs WHERE created_at < SYSDATE - :days";
        
        try {
            $db = Database::getInstance();
            $db->execute($sql, [':days' => $days]);
            
            // Log this action
            $this->auditModel->log(
                AuthController::getUserId(),
                ACTION_DELETE,
                'audit_logs',
                null,
                "Cleared audit logs older than {$days} days"
            );
            
            $_SESSION['success'] = "Successfully cleared logs older than {$days} days";
        } catch (Exception $e) {
            error_log("Clear old logs error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to clear old logs';
        }
        
        Router::redirect('/audit/logs');
    }
    
    // Get user activity summary
    public function userActivity() {
        $userId = $_GET['user_id'] ?? null;
        
        if (!$userId) {
            $_SESSION['error'] = 'User ID is required';
            Router::redirect('/audit/logs');
            return;
        }
        
        // Get user details
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            Router::redirect('/audit/logs');
            return;
        }
        
        // Get all logs for this user
        $logs = $this->auditModel->getAll(['user_id' => $userId]);
        
        $data['user'] = $user;
        $data['logs'] = $logs;
        $data['stats'] = $this->getLogStatistics(['user_id' => $userId]);
        
        require_once __DIR__ . '/../views/audit/user_activity.php';
    }
}
?>