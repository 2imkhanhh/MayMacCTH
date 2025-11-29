<?php
class Product
{
    private $conn;
    private $table = "products";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function get($category_id = null, $name = null)
    {
        $query = "SELECT 
                    p.*,
                    c.name as category_name,
                    pc.color_id, pc.color_name, pc.color_code,
                    pv.size,
                    pi.image, pi.is_primary, pi.sort_order
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN product_colors pc ON pc.product_id = p.product_id
                  LEFT JOIN product_variants pv ON pv.color_id = pc.color_id
                  LEFT JOIN product_images pi ON pi.product_id = p.product_id
                  WHERE p.is_active = 1";

        if ($category_id) $query .= " AND p.category_id = :category_id";
        if ($name) $query .= " AND p.name LIKE :name";

        $query .= " ORDER BY p.product_id DESC";

        $stmt = $this->conn->prepare($query);
        if ($category_id) $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        if ($name) $stmt->bindValue(':name', '%' . $name . '%', PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->groupProductData($results);
    }

    public function getById($id)
    {
        $query = "SELECT 
                    p.*, c.name as category_name,
                    pc.color_id, pc.color_name, pc.color_code,
                    pv.variant_id, pv.size,
                    pi.image_id, pi.image, pi.is_primary, pi.sort_order
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN product_colors pc ON pc.product_id = p.product_id
                  LEFT JOIN product_variants pv ON pv.color_id = pc.color_id
                  LEFT JOIN product_images pi ON pi.product_id = p.product_id
                  WHERE p.product_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($results)) return null;

        return $this->groupProductData($results)[0] ?? null;
    }

    private function groupProductData($rows)
    {
        $products = [];

        foreach ($rows as $row) {
            $pid = $row['product_id'];

            if (!isset($products[$pid])) {
                $products[$pid] = [
                    'product_id'     => $pid,
                    'name'           => $row['name'],
                    'description'    => $row['description'] ?? '',
                    'price'          => $row['price'] ?? 0,
                    'category_id'    => $row['category_id'],
                    'category_name'  => $row['category_name'],
                    'star'           => floatval($row['star'] ?? 5),
                    'review_count'   => intval($row['review_count'] ?? 0),
                    'is_active'      => $row['is_active'],
                    'created_at'     => $row['created_at'],
                    'updated_at'     => $row['updated_at'],
                    'primary_image'  => null,
                    'images'         => [],
                    'colors'         => []
                ];
            }

            if (!empty($row['image'])) {
                $exists = false;
                foreach ($products[$pid]['images'] as $img) {
                    if ($img['image'] === $row['image']) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $products[$pid]['images'][] = [
                        'image_id'   => $row['image_id'] ?? null,
                        'image'      => $row['image'],
                        'is_primary' => (int)($row['is_primary'] ?? 0),
                        'sort_order' => (int)($row['sort_order'] ?? 0)
                    ];

                    if (($row['is_primary'] ?? 0) == 1) {
                        $products[$pid]['primary_image'] = $row['image'];
                    } elseif ($products[$pid]['primary_image'] === null && count($products[$pid]['images']) === 1) {
                        $products[$pid]['primary_image'] = $row['image'];
                    }
                }
            }

            if (!empty($row['color_id'])) {
                $colorId = $row['color_id'];

                if (!isset($products[$pid]['colors'][$colorId])) {
                    $products[$pid]['colors'][$colorId] = [
                        'color_id'   => $colorId,
                        'color_name' => $row['color_name'],
                        'color_code' => $row['color_code'] ?? '#000000',
                        'sizes'      => []
                    ];
                }

                if (!empty($row['size'])) {
                    $size = trim(strtoupper($row['size']));
                    if (!in_array($size, $products[$pid]['colors'][$colorId]['sizes'])) {
                        $products[$pid]['colors'][$colorId]['sizes'][] = $size;
                    }
                }
            }
        }

        foreach ($products as &$p) {
            $p['colors'] = array_values($p['colors']);
            usort($p['images'], function ($a, $b) {
                return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
            });
        }

        return array_values($products);
    }

    public function create($data, $colors, $images, $primaryIndex)
    {
        $this->conn->beginTransaction();
        try {
            $query = "INSERT INTO products SET 
                        name = :name, 
                        description = :description,
                        price = :price,                    
                        category_id = :category_id, 
                        star = 5, 
                        review_count = 0, 
                        is_active = :is_active";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'] ?? '',
                ':price'       => $data['price'] ?? 0,
                ':category_id' => $data['category_id'],
                ':is_active' => $data['is_active'] ?? 1
            ]);
            $productId = $this->conn->lastInsertId();

            foreach ($colors as $c) {
                if (empty($c['name'])) continue;
                $stmt = $this->conn->prepare("INSERT INTO product_colors (product_id, color_name, color_code) VALUES (?, ?, ?)");
                $stmt->execute([$productId, $c['name'], $c['code'] ?? '#000000']);
                $colorId = $this->conn->lastInsertId();

                if (!empty($c['sizes'])) {
                    $sizes = array_map('trim', explode(',', $c['sizes']));
                    foreach ($sizes as $size) {
                        if ($size) {
                            $stmt = $this->conn->prepare("INSERT INTO product_variants (product_id, color_id, size) VALUES (?, ?, ?)");
                            $stmt->execute([$productId, $colorId, strtoupper($size)]);
                        }
                    }
                }
            }

            $uploadDir = __DIR__ . '/../../public/assets/images/upload/';
            foreach ($images as $index => $file) {
                if ($file['error'] !== 0) continue;

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'product_' . $productId . '_' . time() . '_' . $index . '.' . strtolower($ext);
                $path = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $path)) {
                    $isPrimary = ($index == $primaryIndex) ? 1 : 0;
                    $stmt = $this->conn->prepare("INSERT INTO product_images (product_id, image, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$productId, $filename, $isPrimary, $index]);
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Create product error: " . $e->getMessage());
            return false;
        }
    }

    public function update($productId, $data, $colors, $images, $primaryIndex, $existingImages = [])
    {
        $this->conn->beginTransaction();
        try {
            $query = "UPDATE products SET 
                        name = :name,
                        description = :description,
                        price = :price,                         
                        category_id = :category_id,
                        is_active = :is_active
                    WHERE product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':name' => $data['name'],
                ':description' => $data['description'] ?? '',
                ':price'       => $data['price'] ?? 0,
                ':category_id' => $data['category_id'],
                ':is_active' => $data['is_active'] ?? 1,
                ':product_id' => $productId
            ]);

            $this->conn->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$productId]);
            $this->conn->prepare("DELETE FROM product_colors WHERE product_id = ?")->execute([$productId]);

            foreach ($colors as $c) {
                if (empty($c['name'])) continue;
                $stmt = $this->conn->prepare("INSERT INTO product_colors (product_id, color_name, color_code) VALUES (?, ?, ?)");
                $stmt->execute([$productId, $c['name'], $c['code'] ?? '#000000']);
                $colorId = $this->conn->lastInsertId();

                if (!empty($c['sizes'])) {
                    $sizes = array_map('trim', explode(',', $c['sizes']));
                    foreach ($sizes as $size) {
                        if ($size) {
                            $this->conn->prepare("INSERT INTO product_variants (product_id, color_id, size) VALUES (?, ?, ?)")
                                ->execute([$productId, $colorId, strtoupper($size)]);
                        }
                    }
                }
            }

            $uploadDir = __DIR__ . '/../../public/assets/images/upload/';

            $stmt = $this->conn->prepare("SELECT image_id, image FROM product_images WHERE product_id = ?");
            $stmt->execute([$productId]);
            $oldImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($oldImages as $img) {
                if (!in_array($img['image_id'], $existingImages)) {
                    $path = $uploadDir . $img['image'];
                    if (file_exists($path)) @unlink($path);
                    $this->conn->prepare("DELETE FROM product_images WHERE image_id = ?")->execute([$img['image_id']]);
                }
            }

            $sortOrder = 0;
            foreach ($images as $index => $file) {
                if ($file['error'] !== 0) continue;

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'product_' . $productId . '_' . time() . '_' . $index . '.' . strtolower($ext);
                $path = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $path)) {
                    $isPrimary = ($index == $primaryIndex) ? 1 : 0;
                    $this->conn->prepare("INSERT INTO product_images (product_id, image, is_primary, sort_order) VALUES (?, ?, ?, ?)")
                        ->execute([$productId, $filename, $isPrimary, $sortOrder++]);
                }
            }

            if (empty($images) && $primaryIndex >= 0 && isset($existingImages[$primaryIndex])) {
                $this->conn->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?")->execute([$productId]);
                $this->conn->prepare("UPDATE product_images SET is_primary = 1 WHERE image_id = ?")->execute([$existingImages[$primaryIndex]]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Update product error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare("SELECT image FROM product_images WHERE product_id = ?");
            $stmt->execute([$id]);
            $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $uploadDir = __DIR__ . '/../../public/assets/images/upload/';
            foreach ($images as $img) {
                $path = $uploadDir . $img;
                if (file_exists($path)) @unlink($path);
            }

            $this->conn->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM product_colors WHERE product_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM products WHERE product_id = ?")->execute([$id]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Delete product error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll($category_id = null, $name = null)
    {
        $query = "SELECT 
                    p.*,
                    c.name as category_name,
                    pc.color_id, pc.color_name, pc.color_code,
                    pv.size,
                    pi.image, pi.is_primary, pi.sort_order
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN product_colors pc ON pc.product_id = p.product_id
                  LEFT JOIN product_variants pv ON pv.color_id = pc.color_id
                  LEFT JOIN product_images pi ON pi.product_id = p.product_id
                  WHERE 1=1"; 

        if ($category_id) $query .= " AND p.category_id = :category_id";
        if ($name) $query .= " AND p.name LIKE :name";

        $query .= " ORDER BY p.product_id DESC";

        $stmt = $this->conn->prepare($query);
        if ($category_id) $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        if ($name) $stmt->bindValue(':name', '%' . $name . '%', PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->groupProductData($results);
    }
}
