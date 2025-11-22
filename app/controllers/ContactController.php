<?php
require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../models/Contact.php";

class ContactController {
    private $contact;

    public function __construct($db) {
        $this->contact = new Contact($db);
    }

    public function getAll() {
        $data = $this->contact->getAll();
        return ["success" => true, "data" => $data];
    }

    public function create() {
        $this->contact->address      = $_POST['address'] ?? '';
        $this->contact->website      = $_POST['website'] ?? '';
        $this->contact->phone_number = $_POST['phone_number'] ?? '';

        $id = $this->contact->create();
        if ($id) {
            return ["success" => true, "message" => "Thêm liên hệ thành công!", "contact_id" => $id];
        }
        return ["success" => false, "message" => "Thêm thất bại"];
    }

    public function update($id) {
        $info = $this->contact->getById($id);
        if (!$info) {
            return ["success" => false, "message" => "Không tìm thấy liên hệ"];
        }

        $this->contact->address      = $_POST['address'] ?? $info['address'];
        $this->contact->website      = $_POST['website'] ?? $info['website'];
        $this->contact->phone_number = $_POST['phone_number'] ?? $info['phone_number'];

        return $this->contact->update($id)
            ? ["success" => true, "message" => "Cập nhật thành công!"]
            : ["success" => false, "message" => "Cập nhật thất bại"];
    }

    public function delete($id) {
        $info = $this->contact->getById($id);
        if (!$info) {
            return ["success" => false, "message" => "Không tìm thấy liên hệ"];
        }

        return $this->contact->delete($id)
            ? ["success" => true, "message" => "Xóa thành công!"]
            : ["success" => false, "message" => "Xóa thất bại"];
    }
}
?>