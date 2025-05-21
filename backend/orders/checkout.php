<?php
// At the top of the file
require_once '../../backend/exceptions/ShoppingExceptions.php';

use Backend\Exceptions\ShoppingException;
use Backend\Exceptions\ProductNotFoundException;
use Backend\Exceptions\InsufficientStockException;
use Backend\Exceptions\ProductOutOfStockException;
use Backend\Exceptions\ValidationException;
use Backend\Exceptions\DatabaseException;

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
                throw new ShoppingException("Ваш кошик порожній або товари недоступні.", 0, null, 'empty_cart');
            }

            if ($this->stockError) {
                $this->redirectToCart();
            }
        } catch (ShoppingException $e) {
            error_log('Shopping error: ' . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            $this->redirectToCart();
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            $_SESSION['error'] = (new DatabaseException())->getMessage();
            $this->redirectToCart();
        } catch (Exception $e) {
            error_log('Error verifying cart: ' . $e->getMessage());
            $_SESSION['error'] = "Виникла помилка при перевірці кошика.";
            $this->redirectToCart();
        }
    }

    private function validateCartItems() {
        foreach ($_SESSION['cart'] as $cartItem) {
            $product = $this->getProductFromDatabase($cartItem['id']);
            
            if (!$product) {
                throw new ProductNotFoundException($cartItem['id']);
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
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new ProductNotFoundException($productId);
            }
            
            return $product;
        } catch (PDOException $e) {
            throw new DatabaseException("Помилка при отриманні даних товару.", 0, $e);
        }
    }

    private function checkProductStock($product, &$cartItem) {
        if ($product['stock'] < $cartItem['quantity']) {
            if ($product['stock'] > 0) {
                $cartItem['quantity'] = $product['stock'];
                $this->stockError = true;
                throw new InsufficientStockException($product['id'], $product['stock'], $cartItem['quantity']);
            } else {
                throw new ProductOutOfStockException($product['id']);
            }
        }
    }

    public function processForm() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateName();
                $this->validateEmail();
                $this->validatePhone();
                $this->validateAddress();
                $this->validatePaymentMethod();

                $this->saveOrderToSession();
                $this->redirectToOrderProcessing();
            } catch (ValidationException $e) {
                $this->errors[$e->getField()] = $e->getMessage();
            }
        }
    }

    private function validateName() {
        if (empty($_POST['name'])) {
            throw new ValidationException('name', 'Введіть ваше ім\'я');
        } else {
            $this->formData['name'] = trim($_POST['name']);
        }
    }

    private function validateEmail() {
        if (empty($_POST['email'])) {
            throw new ValidationException('email', 'Введіть вашу електронну пошту');
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('email', 'Введіть коректну електронну пошту');
        } else {
            $this->formData['email'] = trim($_POST['email']);
        }
    }

    private function validatePhone() {
        if (empty($_POST['phone'])) {
            throw new ValidationException('phone', 'Введіть ваш номер телефону');
        } else {
            $this->formData['phone'] = trim($_POST['phone']);
        }
    }

    private function validateAddress() {
        if (empty($_POST['address'])) {
            throw new ValidationException('address', 'Введіть вашу адресу доставки');
        } else {
            $this->formData['address'] = trim($_POST['address']);
        }
    }

    private function validatePaymentMethod() {
        if (isset($_POST['payment_method']) && in_array($_POST['payment_method'], ['cash', 'card'])) {
            $this->formData['payment_method'] = $_POST['payment_method'];
        } else {
            throw new ValidationException('payment_method', 'Виберіть спосіб оплати');
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