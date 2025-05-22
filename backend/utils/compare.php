<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/../database/Database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();


    $idsParam = $_GET['products'] ?? '';
    $ids = $idsParam === '' ? [] : explode(',', $idsParam);
    $ids = array_map('intval', $ids);

    if (empty($ids)) {
        $products = [];
    } else {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "
            SELECT p.*,
                   (SELECT image_filename FROM product_images WHERE product_id = p.id LIMIT 1) AS image_filename
            FROM products p
            WHERE p.id IN ($placeholders)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Товари не знайдені');
        }
    }

    $error = null;
} catch (Exception $e) {
    error_log("Compare error: {$e->getMessage()}");
    $products = [];
    $error = $e->getMessage();
}

require __DIR__ . '/../views/compare_view.php';