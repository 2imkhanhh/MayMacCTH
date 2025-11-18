<?php
// app/models/Account.php

require_once __DIR__ . '/../../config/connect.php';

class Account {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();  // <-- LẤY PDO ĐÚNG CÁCH
    }

    public function findByName($name) {
        $sql = "SELECT * FROM accounts WHERE name = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}