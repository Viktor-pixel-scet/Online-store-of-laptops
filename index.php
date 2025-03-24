<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$min_price = 0;
$max_price = PHP_INT_MAX;

if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $min_price = floatval($_GET['min_price']);
}

if (isset($_GET['max_price']) && is_numeric($_GET['max_price']) && $_GET['max_price'] > 0) {
    $max_price = floatval($_GET['max_price']);
}

try {
    $price_range_stmt = $pdo->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE stock > 0");
    $price_range = $price_range_stmt->fetch();

    $default_min = isset($price_range['min_price']) ? floor($price_range['min_price']) : 0;
    $default_max = isset($price_range['max_price']) ? ceil($price_range['max_price']) : 100000;

    if ($min_price === 0) {
        $min_price = $default_min;
    }

    if ($max_price === PHP_INT_MAX) {
        $max_price = $default_max;
    }

    $stmt = $pdo->prepare("
        SELECT p.*, pi.image_filename
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id
        WHERE p.stock > 0 AND p.price >= ? AND p.price <= ?
        ORDER BY p.price
    ");
    $stmt->execute([$min_price, $max_price]);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error fetching products: ' . $e->getMessage());
    $products = [];
    $default_min = 0;
    $default_max = 100000;
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

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Фільтрація за ціною</h5>
        </div>
        <div class="card-body">
            <form method="get" action="index.php" id="price-filter-form" class="row align-items-end">
                <div class="col-md-5 mb-3">
                    <label for="min_price" class="form-label">Мінімальна ціна:</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="min_price" name="min_price"
                               min="<?php echo $default_min; ?>" max="<?php echo $default_max; ?>"
                               value="<?php echo $min_price; ?>">
                        <span class="input-group-text">грн</span>
                    </div>
                </div>
                <div class="col-md-5 mb-3">
                    <label for="max_price" class="form-label">Максимальна ціна:</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="max_price" name="max_price"
                               min="<?php echo $default_min; ?>" max="<?php echo $default_max; ?>"
                               value="<?php echo $max_price; ?>">
                        <span class="input-group-text">грн</span>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <button type="submit" class="btn btn-primary w-100">Застосувати</button>
                </div>
            </form>
            <div class="text-end mt-2">
                <a href="index.php" class="btn btn-outline-secondary btn-sm">Скинути фільтри</a>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Товари за вказаними параметрами не знайдено. <a href="index.php" class="alert-link">Скинути фільтри</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($product['image_filename']); ?>" class="card-img-top"
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const minPriceInput = document.getElementById('min_price');
        const maxPriceInput = document.getElementById('max_price');
        const filterForm = document.getElementById('price-filter-form');

        filterForm.addEventListener('submit', function(event) {
            const minPrice = parseInt(minPriceInput.value);
            const maxPrice = parseInt(maxPriceInput.value);

            if (minPrice > maxPrice) {
                event.preventDefault();
                alert('Мінімальна ціна не може бути більшою за максимальну!');
            }
        });

        minPriceInput.addEventListener('change', function() {
            if (parseInt(minPriceInput.value) > parseInt(maxPriceInput.value)) {
                maxPriceInput.value = minPriceInput.value;
            }
        });
    });
</script>
</body>
</html>