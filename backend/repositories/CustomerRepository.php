<?php

namespace Repositories;

use PDO;

class CustomerRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $customer): int
    {
        $stmt = $this->db->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $customer['name'],
            $customer['email'],
            $customer['phone'],
            $customer['address']
        ]);
        return $this->db->lastInsertId();
    }
}
