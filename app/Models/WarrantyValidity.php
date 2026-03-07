<?php

namespace App\Models;

use App\Core\Database;
use DateTime;
use DateTimeZone;
use PDO;

class WarrantyValidity
{
  private string $table = 'warranty_validities';

  public function registerFromPaidInvoice(int $invoiceId, ?string $paidAt = null): int
  {
    $db = Database::connect();

    $invoiceStmt = $db->prepare(" 
            SELECT i.id, i.quote_id, i.client_id, wo.car_id
            FROM invoices i
            INNER JOIN work_orders wo ON wo.id = i.work_order_id
            WHERE i.id = :id
            LIMIT 1
        ");
    $invoiceStmt->execute(['id' => $invoiceId]);
    $invoice = $invoiceStmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
      return 0;
    }

    $itemsStmt = $db->prepare(" 
            SELECT
                qi.id AS quote_item_id,
                qi.service_id,
                qi.description,
                qi.warranty_time_base,
                s.name AS service_name
            FROM quote_items qi
            LEFT JOIN services s ON s.id = qi.service_id
            WHERE qi.quote_id = :quote_id
              AND qi.item_type = 'service'
              AND qi.has_warranty = 1
              AND COALESCE(qi.warranty_time_base, 0) > 0
            ORDER BY qi.id ASC
        ");
    $itemsStmt->execute(['quote_id' => (int) $invoice['quote_id']]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
      return 0;
    }

    $startDate = $this->resolvePaidAt($paidAt);

    $existsStmt = $db->prepare(
      "SELECT id FROM {$this->table} WHERE invoice_id = :invoice_id AND quote_item_id = :quote_item_id LIMIT 1",
    );

    $insertStmt = $db->prepare(" 
            INSERT INTO {$this->table}
            (invoice_id, quote_item_id, quote_id, client_id, car_id, service_id, service_description, warranty_months, starts_at, expires_at, status, created_at, updated_at)
            VALUES
            (:invoice_id, :quote_item_id, :quote_id, :client_id, :car_id, :service_id, :service_description, :warranty_months, :starts_at, :expires_at, :status, NOW(), NOW())
        ");

    $created = 0;

    foreach ($items as $item) {
      $quoteItemId = (int) ($item['quote_item_id'] ?? 0);
      $warrantyMonths = (int) ($item['warranty_time_base'] ?? 0);

      if ($quoteItemId <= 0 || $warrantyMonths <= 0) {
        continue;
      }

      $existsStmt->execute([
        'invoice_id' => $invoiceId,
        'quote_item_id' => $quoteItemId,
      ]);

      if ($existsStmt->fetch(PDO::FETCH_ASSOC)) {
        continue;
      }

      $expiresAt = $this->addMonths($startDate, $warrantyMonths);
      $serviceDescription = trim(
        (string) ($item['service_name'] ?: $item['description'] ?: 'Servicio'),
      );

      $insertStmt->execute([
        'invoice_id' => $invoiceId,
        'quote_item_id' => $quoteItemId,
        'quote_id' => (int) $invoice['quote_id'],
        'client_id' => (int) $invoice['client_id'],
        'car_id' => (int) $invoice['car_id'],
        'service_id' => !empty($item['service_id']) ? (int) $item['service_id'] : null,
        'service_description' => $serviceDescription,
        'warranty_months' => $warrantyMonths,
        'starts_at' => $startDate,
        'expires_at' => $expiresAt,
        'status' => $this->isExpired($expiresAt) ? 'expired' : 'active',
      ]);

      $created++;
    }

    return $created;
  }

  public function updateExpiredStatuses(): int
  {
    $db = Database::connect();

    $stmt = $db->prepare(" 
            UPDATE {$this->table}
            SET status = 'expired', updated_at = NOW()
            WHERE status = 'active' AND expires_at < NOW()
        ");
    $stmt->execute();

    return $stmt->rowCount();
  }

  public function listByInvoiceId(int $invoiceId): array
  {
    $this->updateExpiredStatuses();

    $db = Database::connect();

    $stmt = $db->prepare(" 
            SELECT
                id,
                service_description,
                warranty_months,
                starts_at,
                expires_at,
                status,
                reminder_generated_at
            FROM {$this->table}
            WHERE invoice_id = :invoice_id
            ORDER BY id ASC
        ");
    $stmt->execute(['invoice_id' => $invoiceId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function all(?string $search = null, ?string $status = null): array
  {
    $this->updateExpiredStatuses();

    $db = Database::connect();

    $query = "
            SELECT
                wv.id,
                wv.invoice_id,
                i.invoice_number,
                wv.service_description,
                wv.warranty_months,
                wv.starts_at,
                wv.expires_at,
                wv.status,
                c.name AS client_name,
                c.phone AS client_phone,
                CONCAT(car.brand, ' ', car.model, ' - ', car.plate) AS car_info,
                car.plate
            FROM {$this->table} wv
            INNER JOIN invoices i ON i.id = wv.invoice_id
            INNER JOIN clients c ON c.id = wv.client_id
            INNER JOIN cars car ON car.id = wv.car_id
            WHERE 1=1
        ";

    $params = [];

    if ($search !== null && trim($search) !== '') {
      $query .= "
                AND (
                    c.name LIKE :search
                    OR car.plate LIKE :search
                    OR CAST(wv.invoice_id AS CHAR) LIKE :search
                    OR wv.service_description LIKE :search
                )
            ";
      $params['search'] = '%' . trim($search) . '%';
    }

    if (in_array($status, ['active', 'expired'], true)) {
      $query .= ' AND wv.status = :status';
      $params['status'] = $status;
    }

    $query .= ' ORDER BY wv.expires_at ASC, wv.id DESC';

    $stmt = $db->prepare($query);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function createExpiredWarrantyReminders(Notification $notificationModel): int
  {
    $this->updateExpiredStatuses();

    $db = Database::connect();

    $stmt = $db->query(" 
            SELECT
                wv.id,
                wv.client_id,
                wv.service_id,
                wv.service_description,
                wv.expires_at,
                c.name AS client_name,
                c.phone AS phone_number
            FROM {$this->table} wv
            INNER JOIN clients c ON c.id = wv.client_id
            WHERE wv.status = 'expired'
              AND wv.reminder_notification_id IS NULL
            ORDER BY wv.expires_at ASC
        ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
      return 0;
    }

    $updateStmt = $db->prepare(" 
            UPDATE {$this->table}
            SET reminder_notification_id = :notification_id,
                reminder_generated_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ");

    $created = 0;

    foreach ($rows as $row) {
      $phone = preg_replace('/\D+/', '', (string) ($row['phone_number'] ?? ''));

      if ($phone === '') {
        continue;
      }

      $expiresAt = !empty($row['expires_at'])
        ? date('d/m/Y', strtotime((string) $row['expires_at']))
        : date('d/m/Y');

      $message = "Estimado(a) {$row['client_name']},\n\n"
        . "Le informamos que la garantía del servicio '{$row['service_description']}' venció el {$expiresAt}.\n\n"
        . "Para mantener su vehículo en óptimas condiciones, le recomendamos programar su próximo mantenimiento preventivo.\n\n"
        . "Quedamos atentos para ayudarle a agendar su cita.\n\n"
        . "📱 WhatsApp: +51979701851";

      $notificationId = $notificationModel->create([
        'client_id' => (int) $row['client_id'],
        'service_id' => !empty($row['service_id']) ? (int) $row['service_id'] : null,
        'phone_number' => $phone,
        'message_content' => $message,
        'status' => 'pending',
        'error_message' => null,
        'whatsapp_message_id' => null,
        'sent_at' => null,
      ]);

      $updateStmt->execute([
        'notification_id' => $notificationId,
        'id' => (int) $row['id'],
      ]);

      $created++;
    }

    return $created;
  }

  private function addMonths(string $dateTime, int $months): string
  {
    $date = new DateTime($dateTime);
    $date->modify('+' . $months . ' months');

    return $date->format('Y-m-d H:i:s');
  }

  private function resolvePaidAt(?string $paidAt): string
  {
    $limaTz = new DateTimeZone('America/Lima');

    if (empty($paidAt)) {
      return (new DateTime('now', $limaTz))->format('Y-m-d H:i:s');
    }

    $date = new DateTime($paidAt);
    $date->setTimezone($limaTz);

    return $date->format('Y-m-d H:i:s');
  }

  private function isExpired(string $expiresAt): bool
  {
    return strtotime($expiresAt) < time();
  }
}
