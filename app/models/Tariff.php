<?php
// models/Tariff.php
require_once __DIR__ . '/../config/database.php';

class Tariff {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM tariffs ORDER BY tariff_name";
        try {
            return $this->db->query($sql);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function getById($tariffId) {
        $sql = "SELECT * FROM tariffs WHERE tariff_id = :tariff_id";
        try {
            $results = $this->db->query($sql, [':tariff_id' => $tariffId]);
            return count($results) > 0 ? $results[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function create($data) {
        $sql = "INSERT INTO tariffs (tariff_id, tariff_name, base_price, description, created_at)
                VALUES (tariff_seq.NEXTVAL, :tariff_name, :base_price, :description, SYSDATE)";
        try {
            $this->db->execute($sql, [
                ':tariff_name' => $data['tariff_name'],
                ':base_price' => $data['base_price'],
                ':description' => $data['description'] ?? null
            ]);
            return $this->db->lastInsertId('tariff_seq');
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function update($tariffId, $data) {
        $sql = "UPDATE tariffs SET tariff_name = :tariff_name, base_price = :base_price,
                description = :description, updated_at = SYSDATE WHERE tariff_id = :tariff_id";
        try {
            return $this->db->execute($sql, [
                ':tariff_id' => $tariffId,
                ':tariff_name' => $data['tariff_name'],
                ':base_price' => $data['base_price'],
                ':description' => $data['description'] ?? null
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>