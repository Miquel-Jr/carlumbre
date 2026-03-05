<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

class WorkOrder
{
    protected $table = 'work_orders';
    protected $activitiesTable = 'work_order_activities';

    public function all()
    {
        $db = Database::connect();

        $stmt = $db->query("SELECT
                wo.*,
                q.id AS quote_number,
                c.name AS client_name,
                CONCAT(car.brand, ' ', car.model, ' - ', car.plate) AS car_info
            FROM {$this->table} wo
            JOIN quotes q ON q.id = wo.quote_id
            JOIN clients c ON c.id = wo.client_id
            JOIN cars car ON car.id = wo.car_id
            ORDER BY wo.created_at DESC");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT
                wo.*,
                q.id AS quote_number,
                c.name AS client_name,
                c.phone,
                CONCAT(car.brand, ' ', car.model, ' - ', car.plate) AS car_info
            FROM {$this->table} wo
            JOIN quotes q ON q.id = wo.quote_id
            JOIN clients c ON c.id = wo.client_id
            JOIN cars car ON car.id = wo.car_id
            WHERE wo.id = :id
            LIMIT 1");

        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByQuoteId($quoteId)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE quote_id = :quote_id LIMIT 1");
        $stmt->execute(['quote_id' => $quoteId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActivities($workOrderId)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM {$this->activitiesTable} WHERE work_order_id = :work_order_id ORDER BY id ASC");
        $stmt->execute(['work_order_id' => $workOrderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addActivity($workOrderId, $description, $quantity = 1, $source = 'manual')
    {
        $db = Database::connect();

        $stmt = $db->prepare("INSERT INTO {$this->activitiesTable}
            (work_order_id, description, quantity, status, source, created_at, updated_at)
            VALUES (:work_order_id, :description, :quantity, :status, :source, NOW(), NOW())");

        return $stmt->execute([
            'work_order_id' => $workOrderId,
            'description' => $description,
            'quantity' => max(1, (int) $quantity),
            'status' => 'pending',
            'source' => $source,
        ]);
    }

    public function updateActivityStatus($activityId, $workOrderId, $status)
    {
        $db = Database::connect();

        $allowedStatuses = ['pending', 'completed'];
        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $stmt = $db->prepare("UPDATE {$this->activitiesTable}
            SET status = :status, updated_at = NOW()
            WHERE id = :id AND work_order_id = :work_order_id");

        return $stmt->execute([
            'id' => (int) $activityId,
            'work_order_id' => (int) $workOrderId,
            'status' => $status,
        ]);
    }

    public function createFromQuote($quoteId)
    {
        $db = Database::connect();

        $existing = $this->findByQuoteId($quoteId);
        if ($existing) {
            return [
                'created' => false,
                'work_order_id' => (int) $existing['id'],
            ];
        }

        try {
            $db->beginTransaction();

            $quoteStmt = $db->prepare('SELECT id, client_id, car_id FROM quotes WHERE id = :id LIMIT 1');
            $quoteStmt->execute(['id' => $quoteId]);
            $quote = $quoteStmt->fetch(PDO::FETCH_ASSOC);

            if (!$quote) {
                $db->rollBack();
                return [
                    'created' => false,
                    'work_order_id' => null,
                ];
            }

            $insertWorkOrderStmt = $db->prepare("INSERT INTO {$this->table}
                (quote_id, client_id, car_id, status, notes, created_at, updated_at)
                VALUES (:quote_id, :client_id, :car_id, :status, :notes, NOW(), NOW())");

            $insertWorkOrderStmt->execute([
                'quote_id' => $quote['id'],
                'client_id' => $quote['client_id'],
                'car_id' => $quote['car_id'],
                'status' => 'pending',
                'notes' => 'Generada automáticamente al aprobar presupuesto #' . $quote['id'],
            ]);

            $workOrderId = (int) $db->lastInsertId();

            $itemsStmt = $db->prepare('SELECT description, quantity FROM quote_items WHERE quote_id = :quote_id ORDER BY id ASC');
            $itemsStmt->execute(['quote_id' => $quote['id']]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($items)) {
                $insertActivityStmt = $db->prepare("INSERT INTO {$this->activitiesTable}
                    (work_order_id, description, quantity, status, source, created_at, updated_at)
                    VALUES (:work_order_id, :description, :quantity, :status, :source, NOW(), NOW())");

                foreach ($items as $item) {
                    $description = trim((string) ($item['description'] ?? ''));
                    if ($description === '') {
                        continue;
                    }

                    $insertActivityStmt->execute([
                        'work_order_id' => $workOrderId,
                        'description' => $description,
                        'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                        'status' => 'pending',
                        'source' => 'quote',
                    ]);
                }
            }

            $db->commit();

            return [
                'created' => true,
                'work_order_id' => $workOrderId,
            ];
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $exception;
        }
    }
}
