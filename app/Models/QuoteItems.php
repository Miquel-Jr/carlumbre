<?php

namespace App\Models;

use App\Core\Database;

class QuoteItems
{

    protected $table = 'quote_items';

    public function create($data)
    {
        $db = Database::connect();

        $stmt = $db->prepare("INSERT INTO {$this->table} (quote_id, service_id, description, quantity, price, subtotal) VALUES (:quote_id, :service_id, :description, :quantity, :price, :subtotal)");
        return $stmt->execute([
            'quote_id' => $data['quote_id'],
            'service_id' => $data['service_id'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'price' => $data['price'],
            'subtotal' => $data['subtotal']
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
