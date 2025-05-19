<?php
session_start();
require_once '../../backend/database/Database.php';

class OrderProcessor {
    private $pdo;
    private $cartItems = [];
    private $totalPrice = 0;
    private $stockError = false;
    private $errors = [];
    private $formData = [
        'name' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'payment_method' => 'cash'
    ];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function processCart() {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            $this->redirectToCart();
        }

        try {
            $this->validateCartItems();
            
            if (empty($this->cartItems)) {
                $_SESSION['error'] = "Ваш кошик порожній або товари недоступні.";
                $this->redirectToCart();
            }

            if ($this->stockError) {
                $this->redirectToCart();
            }
        } catch (PDOException $e) {
            error_log('Error verifying cart: ' . $e->getMessage());
            $_SESSION['error'] = "Виникла помилка при перевірці кошика.";
            $this->redirectToCart();
        }
    }

    private function validateCartItems() {
        foreach ($_SESSION['cart'] as $cartItem) {
            $product = $this->getProductFromDatabase($cartItem['id']);
            
            if (!$product) {
                continue;
            }

            $this->checkProductStock($product, $cartItem);
            
            $cartItem['name'] = $product['name'];
            $cartItem['price'] = $product['price'];

            $this->cartItems[] = $cartItem;
            $this->totalPrice += $cartItem['price'] * $cartItem['quantity'];
        }

        $_SESSION['cart'] = $this->cartItems;
    }

    private function getProductFromDatabase($productId) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }

    private function checkProductStock($product, &$cartItem) {
        if ($product['stock'] < $cartItem['quantity']) {
            if ($product['stock'] > 0) {
                $cartItem['quantity'] = $product['stock'];
                $this->stockError = true;
                $_SESSION['error'] = "Деякі товари були оновлені через обмежену доступність на складі.";
            } else {
                throw new Exception("Product out of stock");
            }
        }
    }

    public function processForm() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateName();
            $this->validateEmail();
            $this->validatePhone();
            $this->validateAddress();
            $this->validatePaymentMethod();

            if (empty($this->errors)) {
                $this->saveOrderToSession();
                $this->redirectToOrderProcessing();
            }
        }
    }

    private function validateName() {
        if (empty($_POST['name'])) {
            $this->errors['name'] = 'Введіть ваше ім\'я';
        } else {
            $this->formData['name'] = trim($_POST['name']);
        }
    }

    private function validateEmail() {
        if (empty($_POST['email'])) {
            $this->errors['email'] = 'Введіть вашу електронну пошту';
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Введіть коректну електронну пошту';
        } else {
            $this->formData['email'] = trim($_POST['email']);
        }
    }

    private function validatePhone() {
        if (empty($_POST['phone'])) {
            $this->errors['phone'] = 'Введіть ваш номер телефону';
        } else {
            $this->formData['phone'] = trim($_POST['phone']);
        }
    }

    private function validateAddress() {
        if (empty($_POST['address'])) {
            $this->errors['address'] = 'Введіть вашу адресу доставки';
        } else {
            $this->formData['address'] = trim($_POST['address']);
        }
    }

    private function validatePaymentMethod() {
        if (isset($_POST['payment_method']) && in_array($_POST['payment_method'], ['cash', 'card'])) {
            $this->formData['payment_method'] = $_POST['payment_method'];
        } else {
            $this->errors['payment_method'] = 'Виберіть спосіб оплати';
        }
    }

    private function saveOrderToSession() {
        $_SESSION['order'] = [
            'items' => $this->cartItems,
            'total_price' => $this->totalPrice,
            'customer' => $this->formData
        ];
    }

    private function redirectToCart() {
        header('Location: cart.php');
        exit;
    }

    private function redirectToOrderProcessing() {
        header('Location: ../../public/process_order.php');
        exit;
    }

    public function getCartItems() {
        return $this->cartItems;
    }

    public function getTotalPrice() {
        return $this->totalPrice;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFormData() {
        return $this->formData;
    }
}

// Main execution
try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $orderProcessor = new OrderProcessor($pdo);
    $orderProcessor->processCart();
    $orderProcessor->processForm();

    $cartItems = $orderProcessor->getCartItems();
    $totalPrice = $orderProcessor->getTotalPrice();
    $errors = $orderProcessor->getErrors();
    $formData = $orderProcessor->getFormData();
} catch (Exception $e) {
    error_log('Error in checkout: ' . $e->getMessage());
    $_SESSION['error'] = "Виникла помилка при обробці замовлення.";
    header('Location: cart.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення - Ноутбук-Маркет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../public/assets/css/style.css" rel="stylesheet">
    <link href="../../public/assets/css/gallery.css" rel="stylesheet">
</head>
<body>
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
                    <a class="nav-link" href="cart.php">
                        Кошик
                        <span class="badge bg-primary">
                            <?php echo count($cartItems); ?>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h1 class="mb-4">Оформлення замовлення</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Інформація для доставки</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Ім'я та прізвище *</label>
                            <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['name']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Електронна пошта *</label>
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['email']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Номер телефону *</label>
                            <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>" required>
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['phone']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Адреса доставки *</label>
                            <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" id="address" name="address" rows="3" required><?php echo htmlspecialchars($formData['address']); ?></textarea>
                            <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['address']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <h5 class="mt-4">Спосіб оплати</h5>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="cash" <?php echo $formData['payment_method'] === 'cash' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="payment_cash">
                                    Готівкою при отриманні
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_card" value="card" <?php echo $formData['payment_method'] === 'card' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="payment_card">
                                    Оплата карткою онлайн
                                </label>
                            </div>
                            <?php if (isset($errors['payment_method'])): ?>
                                <div class="text-danger mt-1">
                                    <?php echo $errors['payment_method']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">Підтвердити замовлення</button>
                            <a href="cart.php" class="btn btn-outline-secondary">Повернутися до кошика</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ваше замовлення</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($cartItems as $item): ?>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">Кількість: <?php echo $item['quantity']; ?></small>
                                </div>
                                <span class="text-muted"><?php echo number_format($item['price'] * $item['quantity'], 2, '.', ' '); ?> грн</span>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Загальна сума</span>
                            <strong><?php echo number_format($totalPrice, 2, '.', ' '); ?> грн</strong>
                        </li>
                    </ul>
                </div>
            </div>
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
                <p>&copy; <?php echo date('Y'); ?> Ноутбук-Маркет. Всі права захищені.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="../../public/assets/js/main.js"></script>
</body>
</html>