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

    public function create($data)
    {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO {$this->table} (client_id, marca, modelo, placa, year) 
                              VALUES (:client_id, :marca, :modelo, :placa, :year)");
        return $stmt->execute([
            'client_id' => $data['client_id'],
            'marca' => $data['marca'],
            'modelo' => $data['modelo'],
            'placa' => $data['placa'],
            'year' => $data['year']
        ]);
    }

    public function update($id, $data)
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE {$this->table} SET marca=:marca, modelo=:modelo, placa=:placa, year=:year WHERE id=:id");
        return $stmt->execute([
            'id' => $id,
            'marca' => $data['marca'],
            'modelo' => $data['modelo'],
            'placa' => $data['placa'],
            'year' => $data['year']
        ]);
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
}