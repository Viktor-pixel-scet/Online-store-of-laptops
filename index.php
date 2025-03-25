<?php
session_start();
require_once 'backend/database/db_connection.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$min_price = 0;
$max_price = 100000;
$screen_sizes = [];
$video_card_types = [];
$storage_types = [];
$min_weight = 0;
$max_weight = 10;



if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $min_price = floatval($_GET['min_price']);
}

if (isset($_GET['max_price']) && is_numeric($_GET['max_price']) && $_GET['max_price'] > 0) {
    $max_price = floatval($_GET['max_price']);
}

if (isset($_GET['screen_sizes']) && is_array($_GET['screen_sizes'])) {
    $screen_sizes = array_map('floatval', $_GET['screen_sizes']);
}

if (isset($_GET['video_card_types']) && is_array($_GET['video_card_types'])) {
    $video_card_types = $_GET['video_card_types'];
}

if (isset($_GET['storage_types']) && is_array($_GET['storage_types'])) {
    $storage_types = $_GET['storage_types'];
}

if (isset($_GET['min_weight']) && is_numeric($_GET['min_weight'])) {
    $min_weight = floatval($_GET['min_weight']);
}

if (isset($_GET['max_weight']) && is_numeric($_GET['max_weight'])) {
    $max_weight = floatval($_GET['max_weight']);
}

try {

    $sql = "SELECT p.*, pi.image_filename
            FROM products p
            LEFT JOIN product_images pi ON p.id = pi.product_id
            WHERE p.stock > 0 
            AND p.price >= ? AND p.price <= ?
            AND p.device_weight >= ? AND p.device_weight <= ?";

    $params = [$min_price, $max_price, $min_weight, $max_weight];

    if (!empty($screen_sizes)) {
        $placeholders = implode(',', array_fill(0, count($screen_sizes), '?'));
        $sql .= " AND p.screen_size IN ($placeholders)";
        $params = array_merge($params, $screen_sizes);
    }

    if (!empty($video_card_types)) {
        $placeholders = implode(',', array_fill(0, count($video_card_types), '?'));
        $sql .= " AND p.video_card_type IN ($placeholders)";
        $params = array_merge($params, $video_card_types);
    }

    if (!empty($storage_types)) {
        $placeholders = implode(',', array_fill(0, count($storage_types), '?'));
        $sql .= " AND p.storage_type IN ($placeholders)";
        $params = array_merge($params, $storage_types);
    }

    $sql .= " ORDER BY p.price";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="public/assets/css/style.css" rel="stylesheet">
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

        #advanced-filter {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        #advanced-filter:hover {
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        #advanced-filter h4 {
            color: #343a40;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        #advanced-filter .mb-3 {
            margin-bottom: 1.5rem;
        }

        #advanced-filter label {
            color: #495057;
            font-weight: 500;
            margin-bottom: 1.0rem;

        }

        /* Price and Weight Filters */
        #advanced-filter .input-group {
            gap: 10px;
        }

        #advanced-filter .input-group input {
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 0.375rem ;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        #advanced-filter .input-group input:focus {
            border-color: #007bff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        #advanced-filter .form-check {
            margin-bottom: 0.5rem;
            gap: 10px;
        }

        #advanced-filter .form-check-input {
            margin-right: 0.5rem;
        }

        #advanced-filter .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }

        #advanced-filter .form-check-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        #advanced-filter .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        #advanced-filter .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: all 0.3s ease;
        }

        #advanced-filter .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        #advanced-filter .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            transition: all 0.3s ease;
        }

        #advanced-filter .btn-secondary:hover {
            background-color: #545b62;
            border-color: #545b62;
        }

        @media (max-width: 768px) {
            .card-body .btn-group {
                flex-direction: column;
            }

            .card-body .btn-group .btn {
                width: 100%;
            }

            #advanced-filter {
                padding: 15px;
            }

            #advanced-filter .btn-group {
                flex-direction: column;
            }

            #advanced-filter .btn-group .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Ноутбук-Маркет</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="backend/orders/cart.php">
                Кошик
                <span class="badge bg-primary"><?php echo count($_SESSION['cart']); ?></span>
            </a>
            <a class="nav-link disabled" href="backend/utils/compare.php" id="compare-link">
                Порівняти
                <span class="badge bg-secondary" id="compare-count">0</span>
            </a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <form id="advanced-filter" method="get">
                <h4>Фільтри</h4>

                <div class="mb-3">
                    <label>Ціна</label>
                    <div class="input-group">
                        <input type="number" name="min_price" class="form-control" placeholder="Від"
                               value="<?php echo $min_price; ?>">
                        <input type="number" name="max_price" class="form-control" placeholder="До"
                               value="<?php echo $max_price; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Діагональ екрану</label>
                    <div>
                        <input type="checkbox" name="screen_sizes[]" value="13.3" id="screen-13">
                        <label for="screen-13">13.3"</label>
                        <input type="checkbox" name="screen_sizes[]" value="15.6" id="screen-15.6">
                        <label for="screen-15.6">15.6"</label>
                        <input type="checkbox" name="screen_sizes[]" value="16" id="screen-16">
                        <label for="screen-16">16"</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Тип відеокарти</label>
                    <div>
                        <input type="checkbox" name="video_card_types[]" value="Integrated" id="integrated">
                        <label for="integrated">Вбудована</label>
                        <input type="checkbox" name="video_card_types[]" value="Discrete" id="discrete">
                        <label for="discrete">Дискретна</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Тип накопичувача</label>
                    <div>
                        <input type="checkbox" name="storage_types[]" value="SSD" id="ssd">
                        <label for="ssd">SSD</label>
                        <input type="checkbox" name="storage_types[]" value="HDD" id="hdd">
                        <label for="hdd">HDD</label>
                        <input type="checkbox" name="storage_types[]" value="SSD+HDD" id="ssd-hdd">
                        <label for="ssd-hdd">SSD+HDD</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Вага пристрою (кг)</label>
                    <div class="input-group">
                        <input type="number" step="0.1" name="min_weight" class="form-control" placeholder="Від"
                               value="<?php echo $min_weight; ?>">
                        <input type="number" step="0.1" name="max_weight" class="form-control" placeholder="До"
                               value="<?php echo $max_weight; ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Застосувати фільтри</button>
                <button type="reset" class="btn btn-secondary">Скинути</button>
            </form>
        </div>

        <div class="col-md-9">
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
                                <img src="<?php echo htmlspecialchars($product['image_filename'] ?? ''); ?>"
                                     class="card-img-top"
                                     alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h5 text-primary">
                                            <?php echo number_format($product['price'], 2, '.', ' '); ?> грн
                                        </span>
                                        <div class="btn-group">
                                            <a href="backend/products/product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-secondary me-2">
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
                                                    href="backend/orders/cart.php?action=add&id=<?php echo $product['id']; ?>"
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
                <a href="backend/utils/compare.php" class="btn btn-primary disabled" id="full-compare-link">Повна таблиця порівняння</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/assets/js/script.js"></script>
<script src="public/assets/js/advanced-filter.js"></script>
</body>
</html>