<?php
class Inventory
{
    private $conn;
    private $table_inventory = "product_inventory";
    private $table_logs = "inventory_logs";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getByProductId($product_id)
    {
        $query = "
            SELECT 
                i.inventory_id, i.variant_id, i.quantity, i.low_stock_threshold, i.warehouse_id,
                w.name AS warehouse_name, w.address,
                pv.size,
                pc.color_name, pc.color_code,
                p.name AS product_name
            FROM product_inventory i
            JOIN product_variants pv ON i.variant_id = pv.variant_id
            JOIN product_colors pc ON pv.color_id = pc.color_id
            JOIN products p ON pv.product_id = p.product_id
            LEFT JOIN warehouses w ON i.warehouse_id = w.warehouse_id
            WHERE pv.product_id = :product_id
            ORDER BY pc.color_name, pv.size
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByVariantId($variant_id, $warehouse_id = null)
    {
        $query = "SELECT * FROM {$this->table_inventory} WHERE variant_id = :variant_id";
        if ($warehouse_id) {
            $query .= " AND warehouse_id = :warehouse_id";
        }
        $query .= " LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':variant_id', $variant_id, PDO::PARAM_INT);
        if ($warehouse_id) {
            $stmt->bindParam(':warehouse_id', $warehouse_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function adjustStock($inventory_id, $quantity, $type, $note = '', $performed_by = null)
    {
        $this->conn->beginTransaction();
        try {
            $change_qty = ($type === 'export') ? -abs($quantity) : abs($quantity);

            $stmt = $this->conn->prepare("SELECT quantity FROM {$this->table_inventory} WHERE inventory_id = ? FOR UPDATE");
            $stmt->execute([$inventory_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new Exception("Không tìm thấy bản ghi kho");
            }

            $old_qty = (int)$row['quantity'];
            $new_qty = $old_qty + $change_qty;

            if ($new_qty < 0) {
                throw new Exception("Số lượng tồn kho không đủ để xuất!");
            }

            $stmt = $this->conn->prepare("
                UPDATE {$this->table_inventory} 
                SET quantity = :qty, updated_at = NOW()
                WHERE inventory_id = :id
            ");
            $stmt->execute([':qty' => $new_qty, ':id' => $inventory_id]);

            $stmt = $this->conn->prepare("
                INSERT INTO {$this->table_logs} 
                (inventory_id, transaction_type, change_quantity, previous_quantity, new_quantity, note, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $defaultNote = ($type === 'import') ? 'Nhập hàng vào kho' : 'Xuất hàng khỏi kho';

            $stmt->execute([
                $inventory_id,
                $type,
                $change_qty,
                $old_qty,
                $new_qty,
                $note ?: $defaultNote
            ]);

            $this->conn->commit();
            return ['success' => true, 'new_quantity' => $new_qty];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function createForVariant($variant_id, $warehouse_id = 1, $initial_qty = 0, $low_threshold = 10)
    {
        $query = "
            INSERT INTO {$this->table_inventory} 
            (variant_id, quantity, low_stock_threshold, warehouse_id, created_at, updated_at)
            VALUES (:v, :q, :t, :w, NOW(), NOW())
        ";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':v' => $variant_id,
            ':q' => $initial_qty,
            ':t' => $low_threshold,
            ':w' => $warehouse_id
        ]);
    }

    public function getAllInventory($filters = [])
    {
        $query = "
            SELECT 
                i.inventory_id, i.variant_id, i.quantity, i.low_stock_threshold, i.warehouse_id,
                w.name AS warehouse_name, w.address,
                p.product_id, p.name AS product_name,
                pc.color_name, pc.color_code,
                pv.size
            FROM product_inventory i
            JOIN product_variants pv ON i.variant_id = pv.variant_id
            JOIN product_colors pc ON pv.color_id = pc.color_id
            JOIN products p ON pv.product_id = p.product_id
            LEFT JOIN warehouses w ON i.warehouse_id = w.warehouse_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['product_name'])) {
            $query .= " AND p.name LIKE :p_name";
            $params[':p_name'] = '%' . trim($filters['product_name']) . '%';
        }

        if (!empty($filters['color'])) {
            $query .= " AND pc.color_name LIKE :color";
            $params[':color'] = '%' . trim($filters['color']) . '%';
        }

        if (!empty($filters['size'])) {
            $query .= " AND pv.size LIKE :size";
            $params[':size'] = '%' . trim($filters['size']) . '%';
        }

        if (!empty($filters['warehouse_id'])) {
            $query .= " AND i.warehouse_id = :w_id";
            $params[':w_id'] = $filters['warehouse_id'];
        }

        if (!empty($filters['low_stock']) && $filters['low_stock'] == '1') {
            $query .= " AND i.quantity <= i.low_stock_threshold";
        }

        $query .= " ORDER BY (i.quantity <= i.low_stock_threshold) DESC, p.name ASC, pv.size ASC";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllWarehouses()
    {
        $query = "SELECT warehouse_id, name, address FROM warehouses ORDER BY warehouse_id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLogs($inventory_id)
    {
        $query = "
                SELECT 
                    log_id, 
                    transaction_type, 
                    change_quantity, 
                    previous_quantity, 
                    new_quantity, 
                    note, 
                    created_at 
                FROM {$this->table_logs} 
                WHERE inventory_id = :id 
                ORDER BY created_at DESC
            ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $inventory_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
