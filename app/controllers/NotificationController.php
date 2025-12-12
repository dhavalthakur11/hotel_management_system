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
?>