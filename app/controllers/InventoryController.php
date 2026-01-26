<?php
require_once __DIR__ . "/../models/Inventory.php";

class InventoryController
{
    private $inventory;

    public function __construct($db)
    {
        $this->inventory = new Inventory($db);
    }

    public function getByProduct($product_id)
    {
        if (!$product_id) {
            return ["success" => false, "message" => "Thiếu product_id"];
        }
        $data = $this->inventory->getByProductId($product_id);
        return ["success" => true, "data" => $data];
    }

    public function getAll()
    {
        $filters = [
            'product_name' => $_GET['product_name'] ?? '',
            'color'        => $_GET['color'] ?? '',
            'size'         => $_GET['size'] ?? '',
            'warehouse_id' => $_GET['warehouse_id'] ?? '',
            'low_stock'    => $_GET['low_stock'] ?? ''
        ];

        $data = $this->inventory->getAllInventory($filters);
        return ["success" => true, "data" => $data];
    }

    public function adjust()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ["success" => false, "message" => "Method not allowed"];
        }

        $inventory_id = (int)($_POST['inventory_id'] ?? 0);
        $quantity     = (int)($_POST['quantity'] ?? 0); 
        $type         = $_POST['type'] ?? 'import';     
        $note         = trim($_POST['note'] ?? '');

        if ($inventory_id <= 0 || $quantity <= 0) {
            return ["success" => false, "message" => "Vui lòng nhập số lượng hợp lệ (> 0)"];
        }

        if (!in_array($type, ['import', 'export'])) {
            return ["success" => false, "message" => "Loại giao dịch không hợp lệ"];
        }

        $result = $this->inventory->adjustStock($inventory_id, $quantity, $type, $note);
        return $result;
    }

    public function getWarehousesList()
    {
        $data = $this->inventory->getAllWarehouses();
        return ["success" => true, "data" => $data];
    }

    public function getHistory()
    {
        $inventory_id = isset($_GET['inventory_id']) ? (int)$_GET['inventory_id'] : 0;

        if ($inventory_id <= 0) {
            return ["success" => false, "message" => "Thiếu inventory_id"];
        }

        $data = $this->inventory->getLogs($inventory_id);
        return ["success" => true, "data" => $data];
    }
}