<?php
class Warehouse {
    private $conn;
    private $table = "warehouses";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY warehouse_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE warehouse_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $phone, $address) {
        $query = "INSERT INTO " . $this->table . " (name, phone, address) VALUES (:name, :phone, :address)";
        $stmt = $this->conn->prepare($query);
        
        $name = htmlspecialchars(strip_tags($name));
        $phone = htmlspecialchars(strip_tags($phone));
        $address = htmlspecialchars(strip_tags($address));

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($id, $name, $phone, $address) {
        $query = "UPDATE " . $this->table . " SET name = :name, phone = :phone, address = :address WHERE warehouse_id = :id";
        $stmt = $this->conn->prepare($query);

        $name = htmlspecialchars(strip_tags($name));
        $phone = htmlspecialchars(strip_tags($phone));
        $address = htmlspecialchars(strip_tags($address));

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE warehouse_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>