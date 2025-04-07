<?php

namespace Repositories;

use PDO;

class ProductRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function reduceStock(int $productId, int $quantity): void
    {
        $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$quantity, $productId]);
    }
}
