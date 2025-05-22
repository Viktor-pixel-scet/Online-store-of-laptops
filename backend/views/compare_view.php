<?php

?>
<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Порівняння товарів – Ноутбук-Маркет</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/public/assets/css/compare.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid mt-5">
  <h1 class="mb-4 text-center">Порівняння ноутбуків</h1>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($error_message) ?>
      <p>Будь ласка, спробуйте пізніше або зверніться в підтримку.</p>
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
                <?= htmlspecialchars($product['name']) ?>
                <button class="btn btn-sm btn-danger remove-compare" data-product-id="<?= htmlspecialchars($product['id']) ?>">
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
                  <img src="<?= htmlspecialchars($product['image_filename']) ?>"
                       alt="<?= htmlspecialchars($product['name']) ?>"
                       class="img-fluid" style="max-height:200px;">
                <?php else: ?>
                  <p>Відсутнє</p>
                <?php endif; ?>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php
         
          $fields = [
            ['label' => 'Ціна',       'key' => 'price',          'suffix' => ' грн'],
            ['label' => 'Опис',       'key' => 'description',    'suffix' => ''],
            ['label' => 'Екран',      'key' => 'screen_size',    'suffix' => ' дюймів'],
            ['label' => 'Відеокарта', 'key' => 'video_card_type','suffix' => ''],
            ['label' => 'Накопичувач','key' => 'storage_type',   'suffix' => ''],
            ['label' => 'Вага',       'key' => 'device_weight',  'suffix' => ' кг'],
            ['label' => 'Наявність',  'key' => 'stock',          'suffix' => ' шт'],
          ];
          foreach ($fields as $col): ?>
            <tr>
              <td class="fw-bold"><?= htmlspecialchars($col['label']) ?></td>
              <?php foreach ($products as $product): ?>
                <?php
                $value = isset($product[$col['key']]) ? $product[$col['key']] : '';
                if ($col['key'] === 'price' && $value !== '') {
                    echo '<td>' . number_format($value, 2, '.', ' ') . $col['suffix'] . '</td>';
                } else {
                    $display = $value !== '' ? htmlspecialchars($value) . $col['suffix'] : '—';
                    echo '<td>' . $display . '</td>';
                }
                ?>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <div class="text-center mt-3">
    <a href="/index.php" class="btn btn-secondary">Назад до каталогу</a>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>


<script src="/public/assets/js/compare.js" defer></script>
</body>
</html>