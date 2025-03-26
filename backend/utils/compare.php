<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

try {
    require_once '../../backend/database/db_connection.php';

    $product_ids = isset($_GET['products']) ? explode(',', $_GET['products']) : [];
    $product_ids = array_map(function($id) {
        $sanitized_id = filter_var($id, FILTER_VALIDATE_INT);
        if ($sanitized_id === false) {
            throw new Exception('Некоректний ідентифікатор товару');
        }
        return $sanitized_id;
    }, $product_ids);

    $products = [];
    if (!empty($product_ids)) {
        try {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $stmt = $pdo->prepare("
                SELECT p.*, 
                (SELECT image_filename FROM product_images WHERE product_id = p.id LIMIT 1) as image_filename 
                FROM products p 
                WHERE id IN ($placeholders)
            ");
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Товари не знайдені');
            }
        } catch (PDOException $e) {
            error_log('Помилка бази даних: ' . $e->getMessage());
            throw new Exception('Не вдалося отримати дані про товари');
        }
    }
} catch (Exception $e) {
    error_log('Помилка в скрипті порівняння: ' . $e->getMessage());

    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Порівняння товарів</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table-compare th, .table-compare td {
            vertical-align: middle;
            text-align: center;
        }
        .table-compare thead th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
<div class="container-fluid mt-5">
    <h1 class="mb-4 text-center">Порівняння ноутбуків</h1>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error_message); ?>
            <p>Виникла технічна проблема. Будь ласка, спробуйте пізніше або зв'яжіться з підтримкою.</p>
        </div>
    <?php elseif (empty($products)): ?>
        <div class="alert alert-info">Немає товарів для порівняння</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-compare">
                <thead>
                <tr>
                    <th>Параметр</th>
                    <?php foreach ($products as $product): ?>
                        <th>
                            <?php echo htmlspecialchars($product['name']); ?>
                            <button class="btn btn-sm btn-danger remove-compare" data-product-id="<?php echo $product['id']; ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Зображення</th>
                    <?php foreach ($products as $product): ?>
                        <th>
                            <?php if (!empty($product['image_filename'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_filename']); ?>"
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="img-fluid" style="max-height: 200px;">
                            <?php else: ?>
                                <p>Зображення відсутнє</p>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <!-- Basic Information -->
                <tr>
                    <td class="fw-bold">Ціна</td>
                    <?php foreach ($products as $product): ?>
                        <td><?php echo number_format($product['price'], 2, '.', ' '); ?> грн</td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Опис</td>
                    <?php foreach ($products as $product): ?>
                        <td><?php echo !empty($product['description']) ? htmlspecialchars($product['description']) : 'Опис відсутній'; ?></td>
                    <?php endforeach; ?>
                </tr>

                <!-- Technical Specifications -->
                <tr>
                    <td class="fw-bold">Екран</td>
                    <?php foreach ($products as $product): ?>
                        <td><?php echo htmlspecialchars($product['screen_size']); ?> дюймів</td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Відеокарта</td>
                    <?php foreach ($products as $product): ?>
                        <td><?php echo htmlspecialchars($product['video_card_type']); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Тип накопичувача</td>
                    <?php foreach ($products as $product): ?>
                        <td><?php echo htmlspecialchars($product['storage_type']); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Вага</td>
                    <?php foreach ($products as $product): ?>
                        <td><?php echo htmlspecialchars($product['device_weight']); ?> кг</td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td class="fw-bold">Наявність на складі</td>
                    <?php foreach ($products as $product): ?>
                        <td><?php echo htmlspecialchars($product['stock']); ?> шт.</td>
                    <?php endforeach; ?>
                </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="mt-3 text-center">
        <a href="../../index.php" class="btn btn-secondary">Назад до каталогу</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const removeButtons = document.querySelectorAll('.remove-compare');

        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                try {
                    const productId = this.getAttribute('data-product-id');

                    let comparisonList;
                    try {
                        comparisonList = JSON.parse(localStorage.getItem('productComparison') || '[]');
                    } catch (e) {
                        console.error('Помилка читання localStorage:', e);
                        comparisonList = [];
                    }

                    const updatedList = comparisonList.filter(id => id != productId);

                    try {
                        localStorage.setItem('productComparison', JSON.stringify(updatedList));
                    } catch (e) {
                        console.error('Помилка запису в localStorage:', e);
                        alert('Не вдалося оновити список порівняння');
                        return;
                    }

                    const currentProducts = new URLSearchParams(window.location.search).get('products');
                    const newProducts = currentProducts.split(',')
                        .filter(id => id != productId)
                        .join(',');

                    if (newProducts) {
                        window.location.href = `compare.php?products=${newProducts}`;
                    } else {
                        window.location.href = 'index.php';
                    }
                } catch (e) {
                    console.error('Критична помилка:', e);
                    alert('Виникла непередбачена помилка');
                }
            });
        });
    });
</script>
</body>
</html>