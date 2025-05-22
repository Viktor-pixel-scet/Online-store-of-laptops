<?php
// public/product.php

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../database/Database.php';

try {
    $db   = new Database();
    $pdo  = $db->getConnection();

    if (!isset($_GET['id'])) {
        throw new Exception('Не передано ідентифікатор товару');
    }
    $product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$product_id) {
        throw new Exception('Некоректний ідентифікатор товару');
    }

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        throw new Exception('Товар не знайдений');
    }

    // обробка зображень
    $images    = array_filter(array_map('trim', explode("\n", $product['image'])));
    $mainImage = $images[0] ?? '/public/assets/img/placeholder.png';

    // передаємо в шаблон
    require __DIR__ . '/../views/product_view.php';

} catch (Exception $e) {
    error_log('Product page error: ' . $e->getMessage());
    $_SESSION['error_message'] = $e->getMessage();
    safeRedirect('/index.php');
}
