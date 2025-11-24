<?php
class Order {
    private $conn;
    private $table_orders = "orders";
    private $table_items  = "order_items";

    // Các field của đơn hàng
    public $name;
    public $phone;
    public $address;
    public $province;
    public $district;
    public $ward;
    public $note;
    public $payment_method;
    public $subtotal;
    public $shipping_fee;
    public $total;
    public $items; // mảng chi tiết sản phẩm

    public function __construct($db) {
        $this->conn = $db;
    }

    // Tạo đơn hàng + chi tiết (có transaction)
    public function create() {
        $this->conn->beginTransaction();

        try {
            // 1. Tạo mã đơn hàng
            $order_code = $this->generateOrderCode();

            // 2. Insert vào bảng orders
            $query = "INSERT INTO {$this->table_orders} 
                      (order_code, name, phone, address, province, district, ward, note, 
                       payment_method, subtotal, shipping_fee, total, 
                       order_status, payment_status, created_at, updated_at)
                      VALUES 
                      (:order_code, :name, :phone, :address, :province, :district, :ward, :note,
                       :payment_method, :subtotal, :shipping_fee, :total,
                       'pending', 'unpaid', NOW(), NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_code', $order_code);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':province', $this->province);
            $stmt->bindParam(':district', $this->district);
            $stmt->bindParam(':ward', $this->ward);
            $stmt->bindParam(':note', $this->note);
            $stmt->bindParam(':payment_method', $this->payment_method);
            $stmt->bindParam(':subtotal', $this->subtotal);
            $stmt->bindParam(':shipping_fee', $this->shipping_fee);
            $stmt->bindParam(':total', $this->total);

            $stmt->execute();
            $order_id = $this->conn->lastInsertId();

            // 3. Insert chi tiết đơn hàng
            $query_item = "INSERT INTO order_items 
               (order_id, product_id, color_name, size, quantity, unit_price, total_price)
               VALUES (:order_id, :product_id, :color_name, :size, :quantity, :unit_price, :total_price)";

            $stmt_item = $this->conn->prepare($query_item);

            foreach ($this->items as $item) {
                $total_price = $item['unit_price'] * $item['quantity'];

                $stmt_item->bindValue(':order_id', $order_id, PDO::PARAM_INT);
                $stmt_item->bindValue(':product_id', $item['product_id'], PDO::PARAM_INT);
                $stmt_item->bindValue(':color_name', $item['color_name'] ?? null);
                $stmt_item->bindValue(':size',       $item['size'] ?? null);
                $stmt_item->bindValue(':quantity',   $item['quantity'], PDO::PARAM_INT);
                $stmt_item->bindValue(':unit_price', $item['unit_price']);
                $stmt_item->bindValue(':total_price', $total_price);

                $stmt_item->execute();
            }

            $this->conn->commit();
            return ['success' => true, 'order_id' => $order_id, 'order_code' => $order_code];

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Order creation failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Tạo đơn hàng thất bại'];
        }
    }

    // Tạo mã đơn hàng đẹp: DH202511240001
    private function generateOrderCode() {
        $today = date('Ymd');
        $query = "SELECT order_code FROM {$this->table_orders} 
                  WHERE order_code LIKE :prefix ORDER BY order_id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $prefix = "DH{$today}%";
        $stmt->bindValue(':prefix', $prefix);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $lastNum = (int)substr($row['order_code'], -4);
            return "DH{$today}" . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }
        return "DH{$today}0001";
    }

    // (Tùy chọn) Lấy danh sách đơn hàng cho admin sau này
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT * FROM {$this->table_orders} ORDER BY order_id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>