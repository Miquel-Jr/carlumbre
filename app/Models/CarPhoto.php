<?php

namespace App\Models;

use App\Core\Database;

class CarPhoto
{
    protected $table = 'car_photos';

    public function all()
    {
        $db = Database::connect();
        $stmt = $db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM car_photos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM car_photos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
