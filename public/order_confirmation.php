<?php
session_start();
require_once '../backend/database/Database.php';
require_once '../backend/commands/OrderRetrievalCommand.php';

$db = new Database();
$pdo = $db->getConnection();

if (!OrderRetrievalCommand::hasValidSession()) {
    header('Location: ../index.php');
    exit;
}

$order_number = $_SESSION['order_number'];
$order = null;
$error_message = null;

try {
    $orderCommand = new OrderRetrievalCommand($pdo, $order_number);
    $order = $orderCommand->execute();
    
} catch (OrderNotFoundException $e) {
    error_log('Order not found: ' . $e->getMessage());
    $error_message = 'Замовлення не знайдено. Можливо, воно було видалено або номер недійсний.';
    
} catch (InvalidArgumentException $e) {
    error_log('Invalid order number: ' . $e->getMessage());
    $error_message = 'Недійсний номер замовлення.';
    
} catch (PDOException $e) {
    error_log('Database error retrieving order: ' . $e->getMessage());
    $error_message = 'Виникла помилка при отриманні інформації про замовлення. Спробуйте пізніше.';
    
} catch (Exception $e) {
    error_log('Unexpected error: ' . $e->getMessage());
    $error_message = 'Виникла непередбачена помилка. Спробуйте пізніше.';
}

if ($error_message && !$order) {
    $_SESSION['error_message'] = $error_message;
    header('Location: ../index.php');
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Помилка!</h4>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                    <hr>
                    <p class="mb-0">
                        <a href="../index.php" class="btn btn-primary">Повернутися на головну</a>
                    </p>
                </div>
            <?php else: ?>
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
                            <h5>Номер вашого замовлення: <strong><?php echo htmlspecialchars($order['order_number']); ?></strong></h5>
                            <p>Будь ласка, збережіть цей номер для подальшого відстеження статусу замовлення.</p>
                            <?php if (isset($order['created_at'])): ?>
                                <small class="text-muted">
                                    Дата замовлення: <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="mt-4">
                            <h5>Деталі замовлення:</h5>
                            <ul class="list-group mb-3">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between lh-sm">
                                        <div>
                                            <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            <small class="text-muted">
                                                Кількість: <?php echo $item['quantity']; ?>
                                                | Ціна за одиницю: <?php echo number_format($item['price'], 2, '.', ' '); ?> грн
                                            </small>
                                        </div>
                                        <span class="text-muted"><?php echo $item['formatted_total']; ?> грн</span>
                                    </li>
                                <?php endforeach; ?>
                                <li class="list-group-item d-flex justify-content-between bg-light">
                                    <div>
                                        <strong>Загальна сума</strong>
                                        <small class="text-muted d-block">Товарів: <?php echo $order['items_count']; ?></small>
                                    </div>
                                    <strong><?php echo $order['formatted_total']; ?> грн</strong>
                                </li>
                            </ul>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Інформація про доставку:</h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="mb-2"><strong>Ім'я:</strong> <?php echo htmlspecialchars($order['customer']['name']); ?></p>
                                            <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($order['customer']['email']); ?></p>
                                            <p class="mb-2"><strong>Телефон:</strong> <?php echo htmlspecialchars($order['customer']['phone']); ?></p>
                                            <p class="mb-0"><strong>Адреса:</strong> <?php echo htmlspecialchars($order['customer']['address']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Спосіб оплати:</h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="mb-0">
                                                <i class="bi bi-credit-card text-primary"></i>
                                                <?php echo OrderRetrievalCommand::getPaymentMethodText($order['customer']['payment_method']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="alert alert-success">
                                <h6 class="alert-heading">Що далі?</h6>
                                <ul class="mb-0">
                                    <li>Ми зв'яжемося з вами протягом робочого дня для підтвердження замовлення</li>
                                    <li>Ви отримаете SMS з деталями доставки</li>
                                    <li>Очікуваний час доставки: 1-3 робочих дні</li>
                                </ul>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <a href="../index.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-arrow-left"></i> Продовжити покупки
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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

<?php
if ($order && !$error_message) {
    OrderRetrievalCommand::clearOrderSession();
}
?>

</body>
</html>
