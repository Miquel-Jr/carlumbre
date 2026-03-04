<?php

namespace App\Models;

use App\Core\Database;

class Product
{
    protected $table = 'products';

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
}
