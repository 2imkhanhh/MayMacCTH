<?php
require_once __DIR__ . "/../../config/connect.php";
require_once __DIR__ . "/../models/Order.php";

class OrderController {
    private $order;

    public function __construct($db) {
        $this->order = new Order($db);
    }

    public function create() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input || !is_array($input) || empty($input['items'])) {
            return ["success" => false, "message" => "Dữ liệu không hợp lệ"];
        }

        $this->order->name           = $input['name'] ?? '';
        $this->order->phone          = $input['phone'] ?? '';
        $this->order->address        = $input['address'] ?? '';
        $this->order->province       = $input['province'] ?? '';
        $this->order->district       = $input['district'] ?? '';
        $this->order->ward           = $input['ward'] ?? '';
        $this->order->note           = $input['note'] ?? '';
        $this->order->payment_method = $input['payment_method'] ?? 'cod';
        $this->order->subtotal       = $input['subtotal'];
        $this->order->shipping_fee   = $input['shipping_fee'];
        $this->order->total          = $input['total'];
        $this->order->items          = $input['items'];

        if (empty($this->order->name) || empty($this->order->phone) || empty($this->order->address)) {
            return ["success" => false, "message" => "Vui lòng điền đầy đủ thông tin"];
        }

        return $this->order->create();
    }
}
?>