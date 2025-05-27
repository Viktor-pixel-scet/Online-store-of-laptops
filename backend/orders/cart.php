<?php
session_start();
require_once '../../backend/database/Database.php';
require_once '../../backend/exceptions/ShoppingExceptions.php';

use Backend\Exceptions\ShoppingException;
use Backend\Exceptions\ProductNotFoundException;
use Backend\Exceptions\InsufficientStockException;
use Backend\Exceptions\ProductOutOfStockException;
use Backend\Exceptions\DatabaseException;

$db = new Database();
$pdo = $db->getConnection();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function updateCartQuantity(&$cart, int $productId, int $change, int $stock)
{
    foreach ($cart as &$item) {
        if ($item['id'] === $productId) {
            $newQuantity = $item['quantity'] + $change;
            if ($newQuantity > $stock) {
                throw new InsufficientStockException($productId, $stock, $newQuantity);
            }
            if ($newQuantity <= 0) {

                return false;
            }
            $item['quantity'] = $newQuantity;
            return true;
        }
    }
    return null;
}

$redirectUrl = 'cart.php';

try {
    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        if ($action === 'clear') {
            $_SESSION['cart'] = [];
            $_SESSION['success'] = "Кошик очищено.";
            header("Location: $redirectUrl");
            exit;
        }

        if (isset($_GET['id'])) {
            $product_id = intval($_GET['id']);
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new ProductNotFoundException($product_id);
            }

            switch ($action) {
                case 'add':
                    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
                    if ($product['stock'] <= 0) {
                        throw new ProductOutOfStockException($product_id);
                    }
                    if ($quantity > $product['stock']) {
                        throw new InsufficientStockException($product_id, $product['stock'], $quantity);
                    }

                    $found = false;
                    foreach ($_SESSION['cart'] as &$item) {
                        if ($item['id'] === $product_id) {
                            $total_requested = $item['quantity'] + $quantity;
                            if ($total_requested > $product['stock']) {
                                throw new InsufficientStockException($product_id, $product['stock'], $total_requested);
                            }
                            $item['quantity'] = $total_requested;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $_SESSION['cart'][] = [
                            'id' => $product['id'],
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'quantity' => $quantity
                        ];
                    }
                    $_SESSION['success'] = "Товар додано до кошика.";
                    break;

                case 'remove':
                    foreach ($_SESSION['cart'] as $key => $item) {
                        if ($item['id'] === $product_id) {
                            unset($_SESSION['cart'][$key]);
                            $_SESSION['cart'] = array_values($_SESSION['cart']);
                            $_SESSION['success'] = "Товар видалено з кошика.";
                            break;
                        }
                    }
                    break;

                case 'increase':
                    if (updateCartQuantity($_SESSION['cart'], $product_id, 1, $product['stock']) === false) {
                        throw new InsufficientStockException($product_id, $product['stock'], 1);
                    }
                    $_SESSION['success'] = "Кількість товару збільшена.";
                    break;

                case 'decrease':
                    $result = updateCartQuantity($_SESSION['cart'], $product_id, -1, $product['stock']);
                    if ($result === false) {
                        // Товар видалено із кошика, уже зроблено в updateCartQuantity
                        $_SESSION['success'] = "Товар видалено з кошика.";
                    } elseif ($result === null) {
                        throw new ProductNotFoundException($product_id);
                    } else {
                        $_SESSION['success'] = "Кількість товару зменшена.";
                    }
                    break;

                default:
                    throw new ShoppingException("Невідома дія: $action");
            }
        }
    }
} catch (ProductNotFoundException $e) {
    error_log('Product not found: ' . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
} catch (InsufficientStockException $e) {
    error_log('Insufficient stock: ' . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
} catch (ProductOutOfStockException $e) {
    error_log('Product out of stock: ' . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error'] = (new DatabaseException())->getMessage();
} catch (ShoppingException $e) {
    error_log('Shopping error: ' . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
} catch (Exception $e) {
    error_log('General error: ' . $e->getMessage());
    $_SESSION['error'] = "Виникла помилка при обробці вашого запиту.";
}

header("Location: $redirectUrl");
exit;


$cart_items = array_values($_SESSION['cart']);

$total_price = 0;
$total_quantity = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
    $total_quantity += $item['quantity'];
}
