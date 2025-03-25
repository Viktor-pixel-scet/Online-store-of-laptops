<?php
try {
    if (empty($order) || !is_array($order)) {
        throw new InvalidArgumentException('Invalid order data');
    }

    $requiredCustomerFields = ['name', 'email', 'phone', 'address', 'payment_method'];
    foreach ($requiredCustomerFields as $field) {
        if (empty($order['customer'][$field])) {
            throw new InvalidArgumentException("Missing required customer field: $field");
        }
    }

    if (empty($order['items']) || !is_array($order['items'])) {
        throw new InvalidArgumentException('No order items provided');
    }

    if (!is_numeric($order['total_price']) || $order['total_price'] <= 0) {
        throw new InvalidArgumentException('Invalid total price');
    }

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
    $updateStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($order['items'] as $item) {
        $stockCheck = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stockCheck->execute([$item['id']]);
        $productStock = $stockCheck->fetchColumn();

        if ($productStock === false || $productStock < $item['quantity']) {
            throw new RuntimeException("Insufficient stock for product: {$item['id']}");
        }

        $stmt->execute([
            $order_id,
            $item['id'],
            $item['quantity'],
            $item['price']
        ]);

        $updateStmt->execute([
            $item['quantity'],
            $item['id']
        ]);
    }

    $pdo->commit();

    $_SESSION['order_number'] = $order_number;
    $_SESSION['cart'] = [];
    header('Location: order_confirmation.php');
    exit;

} catch (InvalidArgumentException $e) {
    $pdo->rollBack();
    error_log('Order validation error: ' . $e->getMessage());
    $_SESSION['error'] = 'Виникла помилка при перевірці даних замовлення. Будь ласка, перевірте введену інформацію.';
    header('Location: checkout.php');
    exit;

} catch (RuntimeException $e) {
    $pdo->rollBack();
    error_log('Order processing error: ' . $e->getMessage());
    $_SESSION['error'] = 'На жаль, деякі товари закінчилися на складі. Перевірте наявність та оновіть замовлення.';
    header('Location: checkout.php');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error'] = 'Технічна помилка при обробці замовлення. Будь ласка, спробуйте пізніше або зверніться до служби підтримки.';
    header('Location: checkout.php');
    exit;
}