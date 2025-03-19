<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

try {
    $stmt = $pdo->query("SELECT * FROM products WHERE stock > 0");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error fetching products: ' . $e->getMessage());
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ноутбук-Маркет - Головна</title>
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
                    <a class="nav-link active" href="index.php">Головна</a>
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
    <h1 class="mb-4">Вітаємо в нашому магазині ноутбуків!</h1>

    <div class="row">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    На даний момент немає доступних товарів.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top"
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="card-text text-primary fw-bold"><?php echo number_format($product['price'], 2, '.', ' '); ?>
                                грн</p>
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-info">Детальніше</a>
                            <a href="cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary">Додати в
                                кошик</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
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