<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$product_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Ноутбук-Маркет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/assets/style.css" rel="stylesheet">
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
                            <?php echo count($_SESSION['cart']); ?>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="text-primary fs-3 fw-bold"><?php echo number_format($product['price'], 2, '.', ' '); ?> грн</p>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <?php if ($product['stock'] > 0): ?>
                <p class="text-success">В наявності: <?php echo $product['stock']; ?> шт.</p>
                <div class="d-grid gap-2">
                    <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary btn-lg">Додати в кошик</a>
                    <a href="index.php" class="btn btn-outline-secondary">Повернутися до списку товарів</a>
                </div>
            <?php else: ?>
                <p class="text-danger">Немає в наявності</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-secondary btn-lg" disabled>Додати в кошик</button>
                    <a href="index.php" class="btn btn-outline-secondary">Повернутися до списку товарів</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h3>Детальний опис</h3>
            <hr>
            <p><?php echo nl2br(htmlspecialchars($product['full_description'])); ?></p>
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