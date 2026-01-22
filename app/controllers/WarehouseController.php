<?php
require_once __DIR__ . '/../models/Warehouse.php';

class WarehouseController {
    private $warehouse;

    public function __construct($db) {
        $this->warehouse = new Warehouse($db);
    }

    public function index() {
        $result = $this->warehouse->read();
        return ['success' => true, 'data' => $result];
    }

    public function show($id) {
        $result = $this->warehouse->readOne($id);
        if ($result) {
            return ['success' => true, 'data' => $result];
        }
        return ['success' => false, 'message' => 'Không tìm thấy kho'];
    }

    public function store() {
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';

        if (empty($name)) {
            return ['success' => false, 'message' => 'Tên kho không được để trống'];
        }

        if ($this->warehouse->create($name, $phone, $address)) {
            return ['success' => true, 'message' => 'Thêm kho thành công'];
        }
        return ['success' => false, 'message' => 'Lỗi khi thêm kho'];
    }

    public function update() {
        $id = $_GET['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';

        if (empty($name) || empty($id)) {
            return ['success' => false, 'message' => 'Dữ liệu không hợp lệ'];
        }

        if ($this->warehouse->update($id, $name, $phone, $address)) {
            return ['success' => true, 'message' => 'Cập nhật thành công'];
        }
        return ['success' => false, 'message' => 'Lỗi cập nhật'];
    }

    public function destroy() {
        $id = $_GET['id'] ?? 0;
        if ($this->warehouse->delete($id)) {
            return ['success' => true, 'message' => 'Xóa thành công'];
        }
        return ['success' => false, 'message' => 'Lỗi khi xóa (Kho có thể đang chứa hàng)'];
    }
}
?>