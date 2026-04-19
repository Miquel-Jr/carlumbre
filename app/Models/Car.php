<?php

namespace App\Models;

use App\Core\Database;

class Car
{
    protected $table = 'cars';

    public function all()
    {
        $db = Database::connect();
        $stmt = $db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByClientId($clientId)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE client_id = :client_id ORDER BY created_at DESC");
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getPhotos($carId)
    {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT * FROM car_photos WHERE car_id = :car_id');
        $stmt->execute(['car_id' => $carId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function addPhoto($carId, $filePath)
    {
        $db = Database::connect();
        $stmt = $db->prepare('INSERT INTO car_photos (car_id, photo_path) VALUES (:car_id, :path)');
        return $stmt->execute([
            'car_id' => $carId,
            'path' => $filePath
        ]);
    }

    public function deletePhoto($photoId)
    {
        $db = Database::connect();
        $stmt = $db->prepare('DELETE FROM car_photos WHERE id = :id');
        return $stmt->execute(['id' => $photoId]);
    }

    public function getCarsWithPlates($search)
    {
        $db = Database::connect();
        $query = 'SELECT *, c.id as car_id, cl.id as client_id, cl.name as client_name FROM cars c INNER JOIN clients cl ON cl.id = c.client_id';
        if ($search) {
            $query .= ' WHERE c.plate LIKE :search OR cl.name LIKE :search OR cl.document_number LIKE :search';
        }
        $query .= ' ORDER BY c.created_at DESC';
        $stmt = $db->prepare($query);
        if ($search) {
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}