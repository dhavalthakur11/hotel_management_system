<?php 
class FeedbackController {
    private $feedbackModel;
    private $bookingModel;
    
    public function __construct() {
        AuthController::requireLogin();
        $this->feedbackModel = new Feedback();
        $this->bookingModel = new Booking();
    }
    
    public function index() {
        $data['feedbacks'] = $this->feedbackModel->getAll();
        require_once __DIR__ . '/../views/feedback/index.php';
    }
    
    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/feedback');
            return;
        }
        
        $feedbackData = [
            'booking_id' => $_POST['booking_id'],
            'customer_id' => $_POST['customer_id'],
            'rating' => $_POST['rating'],
            'comments' => $_POST['comments']
        ];
        
        if ($this->feedbackModel->create($feedbackData)) {
            $_SESSION['success'] = 'Thank you for your feedback';
        } else {
            $_SESSION['error'] = 'Failed to submit feedback';
        }
        
        Router::redirect('/customer/dashboard');
    }
}
?>