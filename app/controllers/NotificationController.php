<?php
class NotificationController {
    private $notificationModel;
    
    public function __construct() {
        AuthController::requireRole(ROLE_ADMIN);
        $this->notificationModel = new Notification();
    }
    
    public function templates() {
        $data['templates'] = $this->notificationModel->getTemplates();
        require_once __DIR__ . '/../views/notification/templates.php';
    }
}

// controllers/AuditLogController.php
class AuditLogController {
    private $auditModel;
    
    public function __construct() {
        AuthController::requireRole(ROLE_ADMIN);
        $this->auditModel = new AuditLog();
    }
    
    public function logs() {
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'action' => $_GET['action'] ?? '',
            'date_from' => $_GET['date_from'] ?? ''
        ];
        
        $data['logs'] = $this->auditModel->getAll($filters);
        require_once __DIR__ . '/../views/audit/logs.php';
    }
}
?>