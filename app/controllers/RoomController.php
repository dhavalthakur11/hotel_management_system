<?php 
class RoomController {
    private $roomModel;
    
    public function __construct() {
        AuthController::requireLogin();
        $this->roomModel = new Room();
    }
    
    public function inventory() {
        $data['rooms'] = $this->roomModel->getAll();
        require_once __DIR__ . '/../views/room/inventory.php';
    }
    
    public function add() {
        // Handled in AdminController
    }
    
    public function update() {
        // Handled in AdminController
    }
    
    public function delete() {
        // Handled in AdminController
    }
}
?>