<?php
session_start();
require_once '../backend/database/Database.php';

$db = new Database();

$pdo = $db->getConnection();

if (!isset($_SESSION['order_number']) || empty($_SESSION['order_number'])) {
    header('Location: index.php');
    exit;
}

$order_number = $_SESSION['order_number'];

try {
    $stmt = $pdo->prepare("
        SELECT o.*, c.name, c.email, c.phone, c.address 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.id 
        WHERE o.order_number = ?
    ");
    $stmt->execute([$order_number]);
    $order_data = $stmt->fetch();

    if (!$order_data) {
        header('Location: index.php');
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT oi.*, p.name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_data['id']]);
    $order_items = $stmt->fetchAll();

    $order = [
        'total_price' => $order_data['total_amount'],
        'customer' => [
            'name' => $order_data['name'],
            'email' => $order_data['email'],
            'phone' => $order_data['phone'],
            'address' => $order_data['address'],
            'payment_method' => $order_data['payment_method']
        ],
        'items' => $order_items
    ];

} catch (PDOException $e) {
    error_log('Error retrieving order: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Замовлення підтверджено - Ноутбук-Маркет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php">Ноутбук-Маркет</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">Головна</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="../backend/orders/cart.php">
                        Кошик
                        <span class="badge bg-primary">
                                <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                            </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Замовлення успішно оформлено!</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <h2 class="mt-3">Дякуємо за ваше замовлення!</h2>
                        <p class="lead">Ваше замовлення успішно оформлено та прийнято до обробки.</p>
                    </div>

                    <div class="alert alert-info">
                        <h5>Номер вашого замовлення: <strong><?php echo $order_number; ?></strong></h5>
                        <p>Будь ласка, збережіть цей номер для подальшого відстеження статусу замовлення.</p>
                    </div>

                    <div class="mt-4">
                        <h5>Деталі замовлення:</h5>
                        <ul class="list-group mb-3">
                            <?php foreach ($order['items'] as $item): ?>
                                <li class="list-group-item d-flex justify-content-between lh-sm">
                                    <div>
                                        <h6 class="my-0"><?php echo $item['name']; ?></h6>
                                        <small class="text-muted">Кількість: <?php echo $item['quantity']; ?></small>
                                    </div>
                                    <span class="text-muted"><?php echo number_format($item['price'] * $item['quantity'], 2, '.', ' '); ?> грн</span>
                                </li>
                            <?php endforeach; ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Загальна сума</span>
                                <strong><?php echo number_format($order['total_price'], 2, '.', ' '); ?> грн</strong>
                            </li>
                        </ul>

                        <h5>Інформація про доставку:</h5>
                        <p><strong>Ім'я:</strong> <?php echo htmlspecialchars($order['customer']['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer']['email']); ?></p>
                        <p><strong>Телефон:</strong> <?php echo htmlspecialchars($order['customer']['phone']); ?></p>
                        <p><strong>Адреса:</strong> <?php echo htmlspecialchars($order['customer']['address']); ?></p>
                        <p><strong>Спосіб оплати:</strong>
                            <?php echo $order['customer']['payment_method'] === 'cash' ? 'Готівкою при отриманні' : 'Оплата карткою онлайн'; ?>
                        </p>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <a href="../index.php" class="btn btn-primary btn-lg">Продовжити покупки</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>НоутбукМаркет</h5>
                <p>Найкращі ноутбуки за найкращими цінами</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p>&copy; 2025 Ноутбук-Маркет. Всі права захищені.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="assets/js/main.js"></script>
</body>
</html>