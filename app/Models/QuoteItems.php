<?php

namespace App\Models;

use App\Core\Database;

class QuoteItems
{

    protected $table = 'quote_items';

    public function create($data)
    {
        $db = Database::connect();

        $stmt = $db->prepare("INSERT INTO {$this->table} (quote_id, service_id, product_id, item_type, description, quantity, price, subtotal, has_warranty, warranty_time_base, reference_image_url) VALUES (:quote_id, :service_id, :product_id, :item_type, :description, :quantity, :price, :subtotal, :has_warranty, :warranty_time_base, :reference_image_url)");
        return $stmt->execute([
            'quote_id' => $data['quote_id'],
            'service_id' => $data['service_id'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'item_type' => $data['item_type'] ?? 'service',
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'price' => $data['price'],
            'subtotal' => $data['subtotal'],
            'has_warranty' => $data['has_warranty'] ?? 0,
            'warranty_time_base' => $data['warranty_time_base'] ?? null,
            'reference_image_url' => $data['reference_image_url'] ?? null
        ]);
    }

    public function getByQuoteId($quoteId)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE quote_id = :quote_id");
        $stmt->execute(['quote_id' => $quoteId]);
        return $stmt->fetchAll();
    }

    public function deleteByQuoteId($quoteId)
    {
        $db = Database::connect();

        $stmt = $db->prepare("DELETE FROM {$this->table} WHERE quote_id = :quote_id");
        return $stmt->execute(['quote_id' => $quoteId]);
    }
}
