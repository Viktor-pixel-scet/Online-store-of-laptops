<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cart_items = [];
$total_price = 0;
$stock_error = false;

try {
    foreach ($_SESSION['cart'] as $cart_item) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$cart_item['id']]);
        $product = $stmt->fetch();

        if (!$product) {
            continue;
        }

        if ($product['stock'] < $cart_item['quantity']) {
            if ($product['stock'] > 0) {
                $cart_item['quantity'] = $product['stock'];
                $stock_error = true;
                $_SESSION['error'] = "Деякі товари були оновлені через обмежену доступність на складі.";
            } else {
                continue;
            }
        }

        $cart_item['name'] = $product['name'];
        $cart_item['price'] = $product['price'];

        $cart_items[] = $cart_item;
        $total_price += $cart_item['price'] * $cart_item['quantity'];
    }

    $_SESSION['cart'] = $cart_items;

    if (empty($cart_items)) {
        $_SESSION['error'] = "Ваш кошик порожній або товари недоступні.";
        header('Location: cart.php');
        exit;
    }

    if ($stock_error) {
        header('Location: cart.php');
        exit;
    }
} catch (PDOException $e) {
    error_log('Error verifying cart: ' . $e->getMessage());
    $_SESSION['error'] = "Виникла помилка при перевірці кошика.";
    header('Location: cart.php');
    exit;
}

$errors = [];
$form_data = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'payment_method' => 'cash'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name'])) {
        $errors['name'] = 'Введіть ваше ім\'я';
    } else {
        $form_data['name'] = trim($_POST['name']);
    }

    if (empty($_POST['email'])) {
        $errors['email'] = 'Введіть вашу електронну пошту';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введіть коректну електронну пошту';
    } else {
        $form_data['email'] = trim($_POST['email']);
    }

    if (empty($_POST['phone'])) {
        $errors['phone'] = 'Введіть ваш номер телефону';
    } else {
        $form_data['phone'] = trim($_POST['phone']);
    }

    if (empty($_POST['address'])) {
        $errors['address'] = 'Введіть вашу адресу доставки';
    } else {
        $form_data['address'] = trim($_POST['address']);
    }

    if (isset($_POST['payment_method']) && in_array($_POST['payment_method'], ['cash', 'card'])) {
        $form_data['payment_method'] = $_POST['payment_method'];
    } else {
        $errors['payment_method'] = 'Виберіть спосіб оплати';
    }

    if (empty($errors)) {
        $_SESSION['order'] = [
            'items' => $cart_items,
            'total_price' => $total_price,
            'customer' => $form_data
        ];

        header('Location: process_order.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення - Ноутбук-Маркет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/assets/style.css" rel="stylesheet">
    <link href="public/assets/gallery.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Ноутбук-Маркет</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Головна</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        Кошик
                        <span class="badge bg-primary">
                            <?php echo count($cart_items); ?>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h1 class="mb-4">Оформлення замовлення</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Інформація для доставки</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Ім'я та прізвище *</label>
                            <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['name']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Електронна пошта *</label>
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['email']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Номер телефону *</label>
                            <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" id="phone" name="phone" value="<?php echo htmlspecialchars($form_data['phone']); ?>" required>
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['phone']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Адреса доставки *</label>
                            <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" id="address" name="address" rows="3" required><?php echo htmlspecialchars($form_data['address']); ?></textarea>
                            <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['address']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <h5 class="mt-4">Спосіб оплати</h5>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="cash" <?php echo $form_data['payment_method'] === 'cash' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="payment_cash">
                                    Готівкою при отриманні
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_card" value="card" <?php echo $form_data['payment_method'] === 'card' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="payment_card">
                                    Оплата карткою онлайн
                                </label>
                            </div>
                            <?php if (isset($errors['payment_method'])): ?>
                                <div class="text-danger mt-1">
                                    <?php echo $errors['payment_method']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">Підтвердити замовлення</button>
                            <a href="cart.php" class="btn btn-outline-secondary">Повернутися до кошика</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ваше замовлення</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($cart_items as $item): ?>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">Кількість: <?php echo $item['quantity']; ?></small>
                                </div>
                                <span class="text-muted"><?php echo number_format($item['price'] * $item['quantity'], 2, '.', ' '); ?> грн</span>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Загальна сума</span>
                            <strong><?php echo number_format($total_price, 2, '.', ' '); ?> грн</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Ноутбук-Маркет</h5>
                <p>Найкращі ноутбуки за найкращими цінами</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p>&copy; 2025 Ноутбук-Маркет. Всі права захищені.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>