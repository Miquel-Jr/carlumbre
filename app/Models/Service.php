<?php

namespace App\Models;

use App\Core\Database;

class Service
{
    protected $table = 'services';

    public function all($search = null)
    {
        $db = Database::connect();

        if ($search) {
            $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE name LIKE :search ORDER BY created_at DESC");
            $stmt->execute(['search' => "%{$search}%"]);
        } else {
            $stmt = $db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $db = Database::connect();

        $stmt = $db->prepare("INSERT INTO {$this->table} (name, description, price, status, has_warranty, warranty_time_base) VALUES (:name, :description, :price, :status, :has_warranty, :warranty_time_base)");
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'status' => $data['status'],
            'has_warranty' => $data['has_warranty'],
            'warranty_time_base' => $data['warranty_time_base']
        ]);
    }

    public function find($id)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function update($id, $data)
    {
        $db = Database::connect();

        $stmt = $db->prepare("UPDATE {$this->table} SET name = :name, description = :description, price = :price, status = :status, has_warranty = :has_warranty, warranty_time_base = :warranty_time_base WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'status' => $data['status'],
            'has_warranty' => $data['has_warranty'],
            'warranty_time_base' => $data['warranty_time_base']
        ]);
    }

    public function delete($id)
    {
        $db = Database::connect();

        $stmt = $db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
