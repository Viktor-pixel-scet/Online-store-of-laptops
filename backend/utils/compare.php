<?php
session_start();
require_once '../../backend/database/db_connection.php';

$product_ids = isset($_GET['products']) ? explode(',', $_GET['products']) : [];
$product_ids = array_map('intval', $product_ids);

$products = [];
if (!empty($product_ids)) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT p.*, 
        (SELECT image_filename FROM product_images WHERE product_id = p.id LIMIT 1) as image_filename 
        FROM products p 
        WHERE id IN ($placeholders)
    ");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Порівняння ноутбуків</h1>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">Немає товарів для порівняння</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
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
                            <img src="<?php echo htmlspecialchars($product['image_filename']); ?>"
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 class="img-fluid" style="max-height: 200px;">
                        </th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Ціна</td>
                    <?php foreach ($products as $product): ?>
                        <td><?php echo number_format($product['price'], 2, '.', ' '); ?> грн</td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Опис</td>
                    <?php foreach ($products as $product): ?>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                    <?php endforeach; ?>
                </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="mt-3">
        <a href="../../index.php" class="btn btn-secondary">Назад до каталогу</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const removeButtons = document.querySelectorAll('.remove-compare');

        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');

                const comparisonList = JSON.parse(localStorage.getItem('productComparison') || '[]');
                const updatedList = comparisonList.filter(id => id != productId);
                localStorage.setItem('productComparison', JSON.stringify(updatedList));

                const currentProducts = new URLSearchParams(window.location.search).get('products');
                const newProducts = currentProducts.split(',')
                    .filter(id => id != productId)
                    .join(',');

                if (newProducts) {
                    window.location.href = `compare.php?products=${newProducts}`;
                } else {
                    window.location.href = 'index.php';
                }
            });
        });
    });
</script>
</body>
</html>