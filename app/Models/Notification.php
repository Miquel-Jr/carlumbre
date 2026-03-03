<?php

namespace App\Models;

use App\Core\Database;

class Notification
{
    protected $table = 'notifications';

    public function all($search = null, $status = null)
    {
        $db = Database::connect();

        $query = "SELECT n.*, c.name as client_name, s.name as service_name 
                  FROM {$this->table} n
                  LEFT JOIN clients c ON n.client_id = c.id
                  LEFT JOIN services s ON n.service_id = s.id
                  WHERE 1=1";
        $params = [];

        if ($search) {
            $query .= " AND (c.name LIKE :search OR n.phone_number LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($status) {
            $query .= " AND n.status = :status";
            $params['status'] = $status;
        }

        $query .= " ORDER BY n.id DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT n.*, c.name as client_name, c.phone as client_phone, s.name as service_name 
                              FROM {$this->table} n
                              LEFT JOIN clients c ON n.client_id = c.id
                              LEFT JOIN services s ON n.service_id = s.id
                              WHERE n.id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $db = Database::connect();

        $stmt = $db->prepare("INSERT INTO {$this->table} 
            (client_id, service_id, phone_number, message_content, status, error_message, whatsapp_message_id, sent_at) 
            VALUES (:client_id, :service_id, :phone_number, :message_content, :status, :error_message, :whatsapp_message_id, :sent_at)");

        $stmt->execute([
            'client_id' => $data['client_id'],
            'service_id' => $data['service_id'] ?? null,
            'phone_number' => $data['phone_number'],
            'message_content' => $data['message_content'],
            'status' => $data['status'] ?? 'pending',
            'error_message' => $data['error_message'] ?? null,
            'whatsapp_message_id' => $data['whatsapp_message_id'] ?? null,
            'sent_at' => $data['sent_at'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }

    public function update($id, array $data): bool
    {
        $db = Database::connect();

        $fields = [];
        $params = ['id' => $id];

        foreach (['status', 'error_message', 'whatsapp_message_id', 'sent_at'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($query);

        return $stmt->execute($params);
    }

    public function delete($id): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getStatistics(): array
    {
        $db = Database::connect();
        $stmt = $db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            FROM {$this->table} 
            ORDER BY id DESC");

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
