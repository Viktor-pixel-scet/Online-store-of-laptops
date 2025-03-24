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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="public/assets/style.css" rel="stylesheet">
</head>
<style>
    .card-body {
        display: flex;
        flex-direction: column;
    }

    .card-body .card-title {
        margin-bottom: 0.5rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .card-body .card-text {
        flex-grow: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        margin-bottom: 0.75rem;
    }

    .card-body .d-flex {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }

    .card-body .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: space-between;
    }

    .card-body .btn-group .btn {
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
    }

    .card-body .btn-group .btn i {
        margin-right: 0.25rem;
    }

    .card-body .h5.text-primary {
        text-align: center;
        margin-bottom: 0.5rem;
    }

    @media (max-width: 768px) {
        .card-body .btn-group {
            flex-direction: column;
        }

        .card-body .btn-group .btn {
            width: 100%;
        }
    }
</style>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Ноутбук-Маркет</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="cart.php">
                Кошик
                <span class="badge bg-primary"><?php echo count($_SESSION['cart']); ?></span>
            </a>
            <a class="nav-link disabled" href="compare.php" id="compare-link">
                Порівняти
                <span class="badge bg-secondary" id="compare-count">0</span>
            </a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Товари за вказаними параметрами не знайдено.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($product['image_filename']); ?>"
                             class="card-img-top"
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 text-primary">
                                    <?php echo number_format($product['price'], 2, '.', ' '); ?> грн
                                </span>
                                <div class="btn-group">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-secondary me-2">
                                        <i class="bi bi-info-circle"></i> Детальніше
                                    </a>
                                    <button
                                            type="button"
                                            class="btn btn-outline-secondary compare-toggle"
                                            data-product-id="<?php echo $product['id']; ?>"
                                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    >
                                        <i class="bi bi-plus-square"></i> Порівняти
                                    </button>
                                    <a
                                            href="cart.php?action=add&id=<?php echo $product['id']; ?>"
                                            class="btn btn-primary"
                                    >
                                        <i class="bi bi-cart-plus"></i> В кошик
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="compareModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Товари для порівняння</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="comparison-list row"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрити</button>
                <a href="compare.php" class="btn btn-primary disabled" id="full-compare-link">Повна таблиця порівняння</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>