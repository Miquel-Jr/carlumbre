<?php

namespace App\Models;

use App\Core\Database;

class Quote
{

    protected $table = 'quotes';

    public function all($search = null)
    {
        $db = Database::connect();

        if ($search) {
            $stmt = $db->prepare("SELECT 
                q.*,
                c.name AS client_name,
                CONCAT(car.brand, ' ', car.model, ' - ', car.plate) AS car_info
            FROM quotes q
            JOIN clients c ON c.id = q.client_id
            JOIN cars car ON car.id = q.car_id
            WHERE c.name LIKE :search
            ORDER BY q.created_at DESC");
            $stmt->execute(['search' => "%{$search}%"]);
        } else {
            $stmt = $db->query("SELECT 
                q.*,
                c.name AS client_name,
                CONCAT(car.brand, ' ', car.model, ' - ', car.plate) AS car_info
            FROM quotes q
            JOIN clients c ON c.id = q.client_id
            JOIN cars car ON car.id = q.car_id
            ORDER BY q.created_at DESC");
        }

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

        $stmt = $db->prepare("INSERT INTO {$this->table} (client_id, car_id, total, status, notes) VALUES (:client_id, :car_id, :total, :status, :notes)");
        $stmt->execute([
            'client_id' => $data['client_id'],
            'car_id' => $data['car_id'],
            'total' => $data['total'],
            'notes' => $data['notes'],
            'status' => $data['status']
        ]);

        return $db->lastInsertId();
    }

    public function update($id, $data)
    {
        $db = Database::connect();

        $stmt = $db->prepare("UPDATE {$this->table} SET client_id = :client_id, car_id = :car_id, total = :total, status = :status, notes = :notes WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'client_id' => $data['client_id'],
            'car_id' => $data['car_id'],
            'total' => $data['total'],
            'status' => $data['status'],
            'notes' => $data['notes']
        ]);
    }

    public function updateStatus($id, $status)
    {
        $db = Database::connect();

        $stmt = $db->prepare("UPDATE {$this->table} SET status = :status WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'status' => $status
        ]);
    }

    public function delete($id)
    {
        $db = Database::connect();

        $stmt = $db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function generatePdf($id)
    {
        $db = Database::connect();

        // Obtener presupuesto
        $stmt = $db->prepare("
            SELECT q.*, 
                c.name as client_name,
                c.email,
                c.phone,
                c.address,
                c.document_type,
                c.document_number,
                car.brand,
                car.model,
                car.plate
            FROM quotes q
            JOIN clients c ON c.id = q.client_id
            JOIN cars car ON car.id = q.car_id
            WHERE q.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
