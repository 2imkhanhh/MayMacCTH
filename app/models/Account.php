<?php
require_once __DIR__ . '/../../config/connect.php';

class Account {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();  
    }

    public function findByName($name) {
        $sql = "SELECT * FROM accounts WHERE name = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}