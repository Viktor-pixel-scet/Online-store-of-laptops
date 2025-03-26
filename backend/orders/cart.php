<?php
session_start();
require_once '../../backend/database/db_connection.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            if ($_GET['action'] === 'add') {
                $product_id = intval($_GET['id']);
                $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

                try {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([$product_id]);
                    $product = $stmt->fetch();

                    if ($product) {
                        if ($quantity > $product['stock']) {
                            $_SESSION['error'] = "На жаль, на складі доступно лише {$product['stock']} шт.";
                            $quantity = $product['stock'];
                        }

                        $found = false;
                        foreach ($_SESSION['cart'] as &$item) {
                            if ($item['id'] == $product_id) {
                                $total_requested = $item['quantity'] + $quantity;

                                if ($total_requested <= $product['stock']) {
                                    $item['quantity'] += $quantity;
                                } else {
                                    $_SESSION['error'] = "На жаль, на складі доступно лише {$product['stock']} шт.";
                                    $item['quantity'] = $product['stock'];
                                }

                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            if ($product['stock'] >= $quantity) {
                                $_SESSION['cart'][] = [
                                    'id' => $product['id'],
                                    'name' => $product['name'],
                                    'price' => $product['price'],
                                    'quantity' => $quantity
                                ];
                            } else {
                                $_SESSION['error'] = "Товар тимчасово відсутній на складі.";
                            }
                        }

                        header('Location: cart.php');
                        exit;
                    }
                } catch (PDOException $e) {
                    error_log('Error processing cart action: ' . $e->getMessage());
                    $_SESSION['error'] = "Виникла помилка при обробці вашого запиту.";
                    header('Location: cart.php');
                    exit;
                }
            }

            if ($_GET['action'] === 'remove') {
                foreach ($_SESSION['cart'] as $key => $item) {
                    if ($item['id'] == $product_id) {
                        unset($_SESSION['cart'][$key]);
                        break;
                    }
                }

                $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                header('Location: cart.php');
                exit;
            }

            if ($_GET['action'] === 'increase') {
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] == $product_id) {
                        // Check if requested quantity is available
                        if ($item['quantity'] < $product['stock']) {
                            $item['quantity']++;
                        } else {
                            $_SESSION['error'] = "На жаль, на складі доступно лише {$product['stock']} шт.";
                        }
                        break;
                    }
                }

                header('Location: cart.php');
                exit;
            }

            if ($_GET['action'] === 'decrease') {
                foreach ($_SESSION['cart'] as $key => &$item) {
                    if ($item['id'] == $product_id) {
                        $item['quantity']--;

                        if ($item['quantity'] <= 0) {
                            unset($_SESSION['cart'][$key]);
                            $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                        }

                        break;
                    }
                }

                header('Location: cart.php');
                exit;
            }
        }
    } catch (PDOException $e) {
        error_log('Error processing cart action: ' . $e->getMessage());
        $_SESSION['error'] = "Виникла помилка при обробці вашого запиту.";
        header('Location: cart.php');
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['cart'] = [];

    header('Location: cart.php');
    exit;
}

$cart_items = array_values($_SESSION['cart']);

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кошик - Ноутбук-Маркет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../public/assets/css/style.css" rel="stylesheet">
    <link href="../../public/assets/css/gallery.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../../index.php">Ноутбук-Маркет</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../../index.php">Головна</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="cart.php">
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
    <h1 class="mb-4">Кошик</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">
            Ваш кошик порожній. <a href="../../index.php" class="alert-link">Перейти до магазину</a>
        </div>
    <?php else: ?>
        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Товар</th>
                            <th class="text-center">Ціна</th>
                            <th class="text-center">Кількість</th>
                            <th class="text-center">Сума</th>
                            <th class="text-center">Дії</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="text-center"><?php echo number_format($item['price'], 2, '.', ' '); ?> грн</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <a href="cart.php?action=decrease&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-secondary">-</a>
                                        <span class="mx-2"><?php echo $item['quantity']; ?></span>
                                        <a href="cart.php?action=increase&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-secondary">+</a>
                                    </div>
                                </td>
                                <td class="text-center"><?php echo number_format($item['price'] * $item['quantity'], 2, '.', ' '); ?> грн</td>
                                <td class="text-center">
                                    <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger">
                                        Видалити
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Загальна сума:</td>
                            <td class="text-center fw-bold"><?php echo number_format($total_price, 2, '.', ' '); ?> грн</td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="cart.php?action=clear" class="btn btn-outline-danger">Очистити кошик</a>
            <a href="checkout.php" class="btn btn-success">Оформити замовлення</a>
        </div>
    <?php endif; ?>
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
<script src="../../public/assets/js/script.js"></script>
</body>
</html>