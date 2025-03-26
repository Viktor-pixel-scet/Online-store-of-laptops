<?php
session_start();
require_once '../../backend/database/db_connection.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart']) || !isset($_SESSION['order']) || empty($_SESSION['order'])) {
    header('Location: cart.php');
    exit;
}

$order = $_SESSION['order'];
$order_number = 'ORDER-' . date('YmdHis') . '-' . mt_rand(1000, 9999);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $order['customer']['name'],
        $order['customer']['email'],
        $order['customer']['phone'],
        $order['customer']['address']
    ]);

    $customer_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, total_amount, payment_method) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $order_number,
        $customer_id,
        $order['total_price'],
        $order['customer']['payment_method']
    ]);

    $order_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

    foreach ($order['items'] as $item) {
        $stmt->execute([
            $order_id,
            $item['id'],
            $item['quantity'],
            $item['price']
        ]);

        $updateStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $updateStmt->execute([$item['quantity'], $item['id']]);
    }

    $pdo->commit();

    $_SESSION['order_number'] = $order_number;

    $_SESSION['cart'] = [];

    header('Location: order_confirmation.php');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();

    error_log('Order processing error: ' . $e->getMessage());

    $_SESSION['error'] = 'Виникла помилка при обробці замовлення. Будь ласка, спробуйте ще раз.';
    header('Location: checkout.php');
    exit;
}