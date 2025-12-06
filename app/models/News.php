<?php
class News
{
    private $conn;
    private $table = "news_articles";
    private $content_table = "news_contents";

    public $id;
    public $title;
    public $slug;
    public $thumbnail;
    public $author;
    public $is_published;
    public $content;
    public $new_category_id;
    public $is_featured = 0;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function get()
    {
        $query = "SELECT a.*, c.content, cat.name as category_name, cat.new_category_id AS category_id
          FROM {$this->table} a 
          LEFT JOIN {$this->content_table} c ON a.id = c.article_id 
          LEFT JOIN news_categories cat ON a.new_category_id = cat.new_category_id
          ORDER BY a.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT a.*, c.content, cat.name AS category_name
                  FROM {$this->table} a 
                  LEFT JOIN {$this->content_table} c ON a.id = c.article_id 
                  LEFT JOIN news_categories cat ON a.new_category_id = cat.new_category_id
                  WHERE a.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create()
    {
        $this->conn->beginTransaction();

        try {
            $query = "INSERT INTO {$this->table} 
                 SET title = :title, slug = :slug, thumbnail = :thumbnail, 
                     author = :author, is_published = :is_published,
                     new_category_id = :new_category_id, is_featured = :is_featured";
            $stmt = $this->conn->prepare($query);

            $this->title = htmlspecialchars(strip_tags($this->title));
            $this->slug = htmlspecialchars(strip_tags($this->slug));
            $this->author = htmlspecialchars(strip_tags($this->author));

            $stmt->bindParam(':title', $this->title);
            $stmt->bindParam(':slug', $this->slug);
            $stmt->bindParam(':thumbnail', $this->thumbnail);
            $stmt->bindParam(':author', $this->author);
            $stmt->bindParam(':is_published', $this->is_published, PDO::PARAM_INT);
            $stmt->bindValue(
                ':new_category_id',
                $this->new_category_id,
                !empty($this->new_category_id) ? PDO::PARAM_INT : PDO::PARAM_NULL
            );
            $stmt->bindParam(':is_featured', $this->is_featured, PDO::PARAM_INT);

            if (!$stmt->execute()) return false;
            $article_id = $this->conn->lastInsertId();

            $query2 = "INSERT INTO {$this->content_table} (article_id, content) VALUES (:id, :content)";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(':id', $article_id, PDO::PARAM_INT);
            $stmt2->bindParam(':content', $this->content);

            if (!$stmt2->execute()) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return $article_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    public function update()
    {
        $this->conn->beginTransaction();
        try {
            $stmtCheck = $this->conn->prepare("SELECT id FROM {$this->table} WHERE slug = :slug AND id != :id");
            $stmtCheck->execute([
                ':slug' => $this->slug,
                ':id'   => $this->id
            ]);

            if ($stmtCheck->rowCount() > 0) {
                throw new Exception("Slug đã được sử dụng bởi bài viết khác!");
            }

            $query = "UPDATE {$this->table} 
            SET title = :title, slug = :slug, thumbnail = :thumbnail,
                author = :author, is_published = :is_published,
                new_category_id = :new_category_id, is_featured = :is_featured
            WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            $this->title  = htmlspecialchars(strip_tags($this->title));
            $this->slug   = htmlspecialchars(strip_tags($this->slug));
            $this->author = htmlspecialchars(strip_tags($this->author));

            $stmt->bindParam(':title', $this->title);
            $stmt->bindParam(':slug', $this->slug);
            $stmt->bindParam(':thumbnail', $this->thumbnail);
            $stmt->bindParam(':author', $this->author);
            $stmt->bindParam(':is_published', $this->is_published, PDO::PARAM_INT);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(
                ':new_category_id',
                $this->new_category_id,
                !empty($this->new_category_id) ? PDO::PARAM_INT : PDO::PARAM_NULL
            );
            $stmt->bindParam(':is_featured', $this->is_featured, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new Exception("Cập nhật bài viết thất bại");
            }

            $query2 = "REPLACE INTO {$this->content_table} (article_id, content) VALUES (:id, :content)";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt2->bindParam(':content', $this->content);

            if (!$stmt2->execute()) {
                throw new Exception("Cập nhật nội dung thất bại");
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Lỗi update bài viết ID {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        $this->conn->beginTransaction();
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();
            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getBySlug($slug)
    {
        $query = "SELECT a.*, c.content, cat.name AS category_name
              FROM {$this->table} a 
              LEFT JOIN {$this->content_table} c ON a.id = c.article_id 
              LEFT JOIN news_categories cat ON a.new_category_id = cat.new_category_id
              WHERE a.slug = :slug AND a.is_published = 1 
              LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
