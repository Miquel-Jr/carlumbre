<?php

namespace App\Models;

use App\Core\Database;

class Client
{
    protected $table = 'clients';

    public function all($search = null)
    {
        $db = Database::connect();

        if ($search) {
            $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE name LIKE :search OR email LIKE :search ORDER BY created_at DESC");
            $stmt->execute(['search' => "%{$search}%"]);
        } else {
            $stmt = $db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
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

    public function findByEmail($email)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByEmailAndId($email, $id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE email = :email AND id != :id");
        $stmt->execute(['email' => $email, 'id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByDocument($document_type, $document_number)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE document_type = :document_type AND document_number = :document_number");
        $stmt->execute(['document_type' => $document_type, 'document_number' => $document_number]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByDocumentAndId($document_type, $document_number, $id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE document_type = :document_type AND document_number = :document_number AND id != :id");
        $stmt->execute(['document_type' => $document_type, 'document_number' => $document_number, 'id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByPhone($phone)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE phone = :phone");
        $stmt->execute(['phone' => $phone]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByPhoneAndId($phone, $id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE phone = :phone AND id != :id");
        $stmt->execute(['phone' => $phone, 'id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO {$this->table} (name, email, phone, address, document_type, document_number) VALUES (:name, :email, :phone, :address, :document_type, :document_number)");
        return $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number']
        ]);
    }

    public function update($id, $data)
    {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE {$this->table} SET name = :name, email = :email, phone = :phone, address = :address, document_type = :document_type, document_number = :document_number WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number']
        ]);
    }

    public function delete($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
