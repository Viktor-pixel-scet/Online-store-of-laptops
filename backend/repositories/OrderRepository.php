<?php

namespace Repositories;

use PDO;

class OrderRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(string $orderNumber, int $customerId, float $total, string $paymentMethod): int
    {
        $stmt = $this->db->prepare("INSERT INTO orders (order_number, customer_id, total_amount, payment_method) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderNumber, $customerId, $total, $paymentMethod]);
        return $this->db->lastInsertId();
    }

    public function addItem(int $orderId, int $productId, int $quantity, float $price): void
    {
        $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $productId, $quantity, $price]);
    }
}
