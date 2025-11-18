<?php
// app/models/Account.php

class Account {
    private $conn;

    public function __construct() {
        require_once '../../config/connect.php'; 
        $this->conn = $conn; 
    }

    public function findByUsernameOrPhone($input) {
        $sql = "SELECT * FROM accounts WHERE email = ? OR phone = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$input, $input]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}