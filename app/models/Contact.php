<?php
class Contact {
    private $conn;
    private $table = "contacts";

    public $contact_id;
    public $address;
    public $website;
    public $phone_number;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY contact_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE contact_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (address, website, phone_number) 
                  VALUES (:address, :website, :phone_number)";

        $stmt = $this->conn->prepare($query);

        $this->sanitize();

        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':website', $this->website);
        $stmt->bindParam(':phone_number', $this->phone_number);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id) {
        $query = "UPDATE " . $this->table . " 
                  SET address = :address, 
                      website = :website, 
                      phone_number = :phone_number 
                  WHERE contact_id = :id";

        $stmt = $this->conn->prepare($query);
        $this->sanitize();

        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':website', $this->website);
        $stmt->bindParam(':phone_number', $this->phone_number);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE contact_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function sanitize() {
        $this->address      = htmlspecialchars(strip_tags($this->address ?? ''));
        $this->website      = htmlspecialchars(strip_tags($this->website ?? ''));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number ?? ''));
    }
}
?>