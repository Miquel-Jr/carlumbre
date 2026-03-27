<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

class Invoice
{
  protected $table = 'invoices';
  protected $itemsTable = 'invoice_items';

  public function all($search = null)
  {
    $db = Database::connect();

    if ($search) {
      $stmt = $db->prepare("SELECT
                  i.*,
                  c.name AS client_name,
                  wo.id AS work_order_number,
                  q.id AS quote_number
              FROM {$this->table} i
              JOIN clients c ON c.id = i.client_id
              JOIN work_orders wo ON wo.id = i.work_order_id
              JOIN quotes q ON q.id = i.quote_id
              WHERE c.name LIKE :search OR c.document_number LIKE :search OR c.phone LIKE :search OR i.invoice_number LIKE :search
              ORDER BY i.created_at DESC");
      $stmt->execute(['search' => "%{$search}%"]);
    } else {
      $stmt = $db->query("SELECT
                  i.*,
                  c.name AS client_name,
                  wo.id AS work_order_number,
                  q.id AS quote_number
              FROM {$this->table} i
              JOIN clients c ON c.id = i.client_id
              JOIN work_orders wo ON wo.id = i.work_order_id
              JOIN quotes q ON q.id = i.quote_id
              ORDER BY i.created_at DESC");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function find($id)
  {
    $db = Database::connect();

    $stmt = $db->prepare("SELECT
                i.*,
                c.name AS client_name,
                c.document_number,
                c.document_type,
                c.phone,
                wo.id AS work_order_number,
                wo.car_id,
                q.id AS quote_number,
                CONCAT(car.brand, ' ', car.model, ' - ', car.plate) AS car_info
            FROM {$this->table} i
            JOIN clients c ON c.id = i.client_id
            JOIN work_orders wo ON wo.id = i.work_order_id
            JOIN quotes q ON q.id = i.quote_id
            JOIN cars car ON car.id = wo.car_id
            WHERE i.id = :id
            LIMIT 1");

    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function getItems($invoiceId)
  {
    $db = Database::connect();

    $stmt = $db->prepare(
      "SELECT * FROM {$this->itemsTable} WHERE invoice_id = :invoice_id ORDER BY id ASC",
    );
    $stmt->execute(['invoice_id' => $invoiceId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function findByWorkOrderId($workOrderId)
  {
    $db = Database::connect();

    $stmt = $db->prepare(
      "SELECT * FROM {$this->table} WHERE work_order_id = :work_order_id LIMIT 1",
    );
    $stmt->execute(['work_order_id' => $workOrderId]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function createFromWorkOrder($workOrderId)
  {
    $db = Database::connect();

    $existing = $this->findByWorkOrderId($workOrderId);
    if ($existing) {
      return [
        'created' => false,
        'invoice_id' => (int) $existing['id'],
        'message' => 'La factura ya existe para esta OT.',
      ];
    }

    try {
      $db->beginTransaction();

      $workOrderStmt = $db->prepare(
        'SELECT id, quote_id, client_id, status FROM work_orders WHERE id = :id LIMIT 1',
      );
      $workOrderStmt->execute(['id' => $workOrderId]);
      $workOrder = $workOrderStmt->fetch(PDO::FETCH_ASSOC);

      if (!$workOrder) {
        $db->rollBack();
        return [
          'created' => false,
          'invoice_id' => null,
          'message' => 'Orden de trabajo no encontrada.',
        ];
      }

      if (($workOrder['status'] ?? 'pending') !== 'completed') {
        $db->rollBack();
        return [
          'created' => false,
          'invoice_id' => null,
          'message' => 'La OT debe estar culminada para facturar.',
        ];
      }

      $activitiesStmt = $db->prepare(
        'SELECT description, quantity, unit_price, source FROM work_order_activities WHERE work_order_id = :work_order_id ORDER BY id ASC',
      );
      $activitiesStmt->execute(['work_order_id' => $workOrderId]);
      $activities = $activitiesStmt->fetchAll(PDO::FETCH_ASSOC);

      if (empty($activities)) {
        $db->rollBack();
        return [
          'created' => false,
          'invoice_id' => null,
          'message' => 'No hay actividades registradas para facturar.',
        ];
      }

      $total = 0.0;
      foreach ($activities as $activity) {
        $quantity = max(1, (int) ($activity['quantity'] ?? 1));
        $unitPrice = (float) ($activity['unit_price'] ?? 0);
        $total += $quantity * $unitPrice;
      }

      $insertInvoiceStmt = $db->prepare("INSERT INTO {$this->table}
                (work_order_id, quote_id, client_id, total, status, issued_at, created_at, updated_at)
                VALUES (:work_order_id, :quote_id, :client_id, :total, :status, NOW(), NOW(), NOW())");

      $insertInvoiceStmt->execute([
        'work_order_id' => $workOrder['id'],
        'quote_id' => $workOrder['quote_id'],
        'client_id' => $workOrder['client_id'],
        'total' => round($total, 2),
        'status' => 'issued',
      ]);

      $invoiceId = (int) $db->lastInsertId();

      $insertItemStmt = $db->prepare("INSERT INTO {$this->itemsTable}
                (invoice_id, description, quantity, unit_price, subtotal, source, created_at, updated_at)
                VALUES (:invoice_id, :description, :quantity, :unit_price, :subtotal, :source, NOW(), NOW())");

      foreach ($activities as $activity) {
        $quantity = max(1, (int) ($activity['quantity'] ?? 1));
        $unitPrice = (float) ($activity['unit_price'] ?? 0);
        $subtotal = $quantity * $unitPrice;

        $insertItemStmt->execute([
          'invoice_id' => $invoiceId,
          'description' => trim((string) ($activity['description'] ?? 'Actividad')),
          'quantity' => $quantity,
          'unit_price' => round($unitPrice, 2),
          'subtotal' => round($subtotal, 2),
          'source' => ($activity['source'] ?? 'manual') === 'quote' ? 'quote' : 'manual',
        ]);
      }

      $db->commit();

      return [
        'created' => true,
        'invoice_id' => $invoiceId,
        'message' => 'Factura generada correctamente.',
      ];
    } catch (Throwable $exception) {
      if ($db->inTransaction()) {
        $db->rollBack();
      }

      throw $exception;
    }
  }

  public function updateInvoiceNumber($id, $invoiceNumber)
  {
    $db = Database::connect();

    $stmt = $db->prepare("UPDATE {$this->table} 
            SET invoice_number = :invoice_number, updated_at = NOW() 
            WHERE id = :id");

    return $stmt->execute([
      'id' => $id,
      'invoice_number' => trim($invoiceNumber) ?: null,
    ]);
  }

  public function findByInvoiceNumber(string $invoiceNumber)
  {
    $db = Database::connect();

    $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE invoice_number = :invoice_number LIMIT 1");
    $stmt->execute([
      'invoice_number' => trim($invoiceNumber),
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function updateStatus($id, $status)
  {
    $db = Database::connect();

    $allowedStatuses = ['issued', 'paid', 'cancelled'];
    if (!in_array($status, $allowedStatuses, true)) {
      return false;
    }

    $stmt = $db->prepare("UPDATE {$this->table} 
            SET status = :status, updated_at = NOW() 
            WHERE id = :id");

    return $stmt->execute([
      'id' => $id,
      'status' => $status,
    ]);
  }

  public function deleteWithItems($id)
  {
    $db = Database::connect();

    try {
      $db->beginTransaction();

      // Eliminar los items de la factura
      $deleteItemsStmt = $db->prepare(
        "DELETE FROM {$this->itemsTable} WHERE invoice_id = :invoice_id",
      );
      $deleteItemsStmt->execute(['invoice_id' => $id]);

      // Eliminar la factura
      $deleteInvoiceStmt = $db->prepare("DELETE FROM {$this->table} WHERE id = :id");
      $deleteInvoiceStmt->execute(['id' => $id]);

      $db->commit();

      return true;
    } catch (Throwable $exception) {
      if ($db->inTransaction()) {
        $db->rollBack();
      }

      throw $exception;
    }
  }
}
