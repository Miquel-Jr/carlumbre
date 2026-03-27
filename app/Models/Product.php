<?php

namespace App\Models;

use App\Core\Database;

class Product
{
    protected $table = 'products';

    public function create(array $data): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO {$this->table} (name, description, category, image_2d, model_3d) VALUES (:name, :description, :category, :image_2d, :model_3d)");

        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'category' => $data['category'],
            'image_2d' => $data['image_2d'],
            'model_3d' => $data['model_3d'],
        ]);
    }

    public function find($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function all($search = null, $category = null)
    {
        $db = Database::connect();

        $conditions = [];
        $params = [];

        if (!empty($search)) {
            $conditions[] = '(name LIKE :search OR description LIKE :search)';
            $params['search'] = "%{$search}%";
        }

        if (!empty($category)) {
            $conditions[] = 'category = :category';
            $params['category'] = $category;
        }

        $sql = "SELECT * FROM {$this->table}";

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getCategories(): array
    {
        $db = Database::connect();
        $stmt = $db->query("SELECT DISTINCT category FROM {$this->table} WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function updateImage2D(int $id, string $imageUrl): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE {$this->table} SET image_2d = :image_2d, updated_at = NOW() WHERE id = :id");

        return $stmt->execute([
            'image_2d' => $imageUrl,
            'id' => $id,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE {$this->table} SET name = :name, description = :description, category = :category, model_3d = :model_3d, image_2d = :image_2d, updated_at = NOW() WHERE id = :id");

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'],
            'category' => $data['category'],
            'model_3d' => $data['model_3d'],
            'image_2d' => $data['image_2d'],
        ]);
    }

    public function delete(int $id): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM {$this->table} WHERE id = :id");

        return $stmt->execute(['id' => $id]);
    }

    public function hasQuoteReferences(int $id): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM quote_items WHERE product_id = :id");
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
