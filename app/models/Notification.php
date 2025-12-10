<?php 
class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function send($data) {
        $sql = "INSERT INTO notifications (notification_id, user_id, type, subject,
                message, sent_at, status, created_at) VALUES (notification_seq.NEXTVAL,
                :user_id, :type, :subject, :message, SYSDATE, :status, SYSDATE)";
        try {
            return $this->db->execute($sql, [
                ':user_id' => $data['user_id'],
                ':type' => $data['type'],
                ':subject' => $data['subject'],
                ':message' => $data['message'],
                ':status' => 'sent'
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function getTemplates() {
        $sql = "SELECT * FROM notification_templates ORDER BY template_name";
        try {
            return $this->db->query($sql);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>