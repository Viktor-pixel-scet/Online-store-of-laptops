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

try {
    require_once '../../backend/database/db_connection.php';

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
</head>
<body>

<?php
if (isset($_SESSION['error_message'])): ?>
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
        <div class="row">
            <div class="col-md-6">
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
                    $mainImage = '../../public/assets/img/placeholder.png'; // Шлях до зображення-заглушки
                    $allImages = '[]';
                }
                ?>
                <img src="<?php echo htmlspecialchars($mainImage); ?>" class="img-fluid rounded"
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     data-all-images='<?php echo $allImages; ?>'>
            </div>
            <div class="col-md-6">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="text-primary fs-3 fw-bold">
                    <?php
                    echo number_format(
                        max(0, floatval($product['price'])),
                        2,
                        '.',
                        ' '
                    );
                    ?> грн
                </p>
                <p><?php echo htmlspecialchars($product['description'] ?? 'Опис відсутній'); ?></p>

                <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                    <p class="text-success">В наявності: <?php echo intval($product['stock']); ?> шт.</p>
                    <div class="d-grid gap-2">
                        <a href="../orders/cart.php?action=add&id=<?php echo $product['id']; ?>" class="btn btn-primary btn-lg">Додати в кошик</a>
                        <a href="../../index.php" class="btn btn-outline-secondary">Повернутися до списку товарів</a>
                    </div>
                <?php else: ?>
                    <p class="text-danger">Немає в наявності</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-secondary btn-lg" disabled>Додати в кошик</button>
                        <a href="../../index.php" class="btn btn-outline-secondary">Повернутися до списку товарів</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <h3>Детальний опис</h3>
                <hr>
                <p>
                    <?php
                    echo nl2br(htmlspecialchars($product['full_description'] ?? 'Детальний опис відсутній'));
                    ?>
                </p>
            </div>
        </div>
        <?php
    } catch (Exception $e) {
        error_log('Помилка рендерингу сторінки товару: ' . $e->getMessage());
        ?>
        <div class="alert alert-danger">
            Виникла технічна проблема при відображенні сторінки товару.
            Будь ласка, спробуйте пізніше або зв'яжіться з підтримкою.
        </div>
    <?php } ?>
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