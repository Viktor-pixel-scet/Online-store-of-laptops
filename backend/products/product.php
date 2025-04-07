<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

function safeRedirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    } else {
        echo "<script>window.location.href = '$url';</script>";
        exit;
    }
}

function formatProductPrice($price) {
    return number_format(max(0, floatval($price)), 2, '.', ' ');
}

function getProductAvailabilityClass($stock) {
    if ($stock > 10) return 'text-success';
    if ($stock > 0) return 'text-warning';
    return 'text-danger';
}

function renderProductBadges($product) {
    $badges = [];
    if (isset($product['is_new']) && $product['is_new']) {
        $badges[] = '<span class="badge bg-success me-2">Новинка</span>';
    }
    if (isset($product['discount']) && $product['discount'] > 0) {
        $badges[] = '<span class="badge bg-danger me-2">-' . $product['discount'] . '%</span>';
    }
    return implode('', $badges);
}

try {
    require_once '../../backend/database/Database.php';

    $db = new Database();

    $pdo = $db->getConnection();

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (!isset($_GET['id'])) {
        throw new Exception('Не передано ідентифікатор товару');
    }

    $product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($product_id === false || $product_id === null) {
        throw new Exception('Некоректний ідентифікатор товару');
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception('Товар не знайдений');
        }
    } catch (PDOException $e) {
        error_log('Помилка бази даних: ' . $e->getMessage());
        throw new Exception('Не вдалося отримати дані про товар');
    }
} catch (Exception $e) {
    error_log('Помилка на сторінці товару: ' . $e->getMessage());

    $_SESSION['error_message'] = $e->getMessage();

    safeRedirect('../../index.php');
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Ноутбук-Маркет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../public/assets/css/style.css" rel="stylesheet">
    <link href="../../public/assets/css/gallery.css" rel="stylesheet">
    <style>
        .image-thumbnails img {
            transition: all 0.3s ease;
        }
        .image-thumbnails img:hover {
            opacity: 0.7;
            transform: scale(1.05);
        }
        .image-zoom-container {
            position: fixed;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            width: 300px;
            height: 300px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border: 2px solid #007bff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1000;
            pointer-events: none;
        }
        .main-product-image,
        .image-thumbnails img {
            cursor: zoom-in;
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php
        echo htmlspecialchars($_SESSION['error_message']);
        unset($_SESSION['error_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

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
                    <a class="nav-link" href="../orders/cart.php">
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
    <?php try { ?>
        <?php
        try {
            $images = explode("\n", $product['image']);
            $mainImage = trim($images[0]);

            if (empty($mainImage)) {
                throw new Exception('Зображення товару відсутнє');
            }

            $allImages = htmlspecialchars(json_encode(array_map('trim', $images)));
        } catch (Exception $e) {
            error_log('Помилка обробки зображень: ' . $e->getMessage());
            $mainImage = '../../public/assets/img/placeholder.png';
            $allImages = '[]';
        }
        ?>

        <div class="row">
            <div class="col-md-6">
                <div class="gallery-slider position-relative">
                    <img src="<?php echo htmlspecialchars($mainImage); ?>"
                         class="img-fluid rounded main-product-image"
                         alt="<?php echo htmlspecialchars($product['name']); ?>">

                    <?php if (count($images) > 1): ?>
                        <div class="image-thumbnails mt-3 d-flex">
                            <?php foreach ($images as $img): ?>
                                <img src="<?php echo htmlspecialchars(trim($img)); ?>"
                                     class="img-thumbnail me-2"
                                     style="max-width: 80px; cursor: pointer;"
                                     onclick="changeMainImage(this)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="product-header mb-4">
                    <?php echo renderProductBadges($product); ?>
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                </div>

                <p class="text-primary fs-3 fw-bold">
                    <?php echo formatProductPrice($product['price']); ?> грн
                </p>

                <div class="product-meta mb-4">
                    <p><?php echo htmlspecialchars($product['description'] ?? 'Опис відсутній'); ?></p>

                    <div class="stock-info">
                        <p class="<?php echo getProductAvailabilityClass($product['stock'] ?? 0); ?>">
                            <?php
                            if (isset($product['stock']) && $product['stock'] > 0) {
                                echo "В наявності: " . intval($product['stock']) . " шт.";
                            } else {
                                echo "Немає в наявності";
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <div class="product-actions">
                    <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                        <form action="../orders/cart.php" method="get">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                            <div class="quantity-selector mb-3">
                                <label for="quantity" class="form-label">Кількість:</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">-</button>
                                    <input type="number"
                                           class="form-control text-center"
                                           id="quantity"
                                           name="quantity"
                                           value="1"
                                           min="1"
                                           max="<?php echo $product['stock']; ?>">
                                    <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">+</button>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Додати в кошик
                                </button>
                                <a href="../../index.php" class="btn btn-outline-secondary">Повернутися до списку товарів</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="d-grid gap-2">
                            <button class="btn btn-secondary btn-lg" disabled>Немає в наявності</button>
                            <a href="../../index.php" class="btn btn-outline-secondary">Повернутися до списку товарів</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-gradient-primary py-3">
                        <h3 class="card-title text-white mb-0 font-weight-bold">
                            <i class="fas fa-info-circle mr-2"></i>Детальний опис
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <?php
                        $full_description = $product['full_description'] ?? 'Детальний опис відсутній';
                        ?>
                        <div class="description-container">
                            <p class="card-text text-dark description-text" style="
                        font-size: 1.1rem;
                        line-height: 1.8;
                        letter-spacing: 0.03em;
                        text-align: justify;
                        font-family: 'Arial', 'Helvetica Neue', sans-serif;
                        color: #333;
                        background-color: #f8f9fa;
                        border-left: 4px solid #007bff;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    ">
                                <?php echo nl2br(htmlspecialchars($full_description)); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } catch (Exception $e) {
        error_log('Помилка рендерингу сторінки товару: ' . $e->getMessage());
        ?>
        <div class="alert alert-danger alert-dismissible fade show shadow" role="alert">
            <strong><i class="fas fa-exclamation-triangle mr-2"></i>Технічна проблема!</strong>
            Виникла помилка при відображенні сторінки товару.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php } ?>

    <style>
        .bg-gradient-primary {
            background: linear-gradient(to right, #007bff, #0056b3);
        }
    </style>
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

<div class="image-zoom-container"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="../../public/assets/js/main.js"></script>

<script>
    function changeMainImage(thumbnail) {
        const mainImage = document.querySelector('.main-product-image');
        mainImage.src = thumbnail.src;
    }

    function changeQuantity(delta) {
        const quantityInput = document.getElementById('quantity');
        let currentValue = parseInt(quantityInput.value);
        let newValue = currentValue + delta;

        if (newValue >= parseInt(quantityInput.min) && newValue <= parseInt(quantityInput.max)) {
            quantityInput.value = newValue;
        }
    }
</script>
</body>
</html>