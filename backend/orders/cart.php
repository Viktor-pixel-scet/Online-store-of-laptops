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

/**
 * Логування дій користувача з кошиком
 */
function logAction(string $message): void
{
    $logFile = __DIR__ . '/cart_actions.log';
    $date = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);
}

/**
 * Оновлення кількості товару у кошику.
 * Видаляє товар, якщо нова кількість <= 0.
 * 
 * @param array &$cart посилання на масив кошика
 * @param int $productId ID товару
 * @param int $change зміна кількості (плюс або мінус)
 * @param int $stock доступний запас товару
 * @return bool|null true - кількість оновлена, false - товар видалено, null - товар не знайдено
 * @throws InsufficientStockException при перевищенні запасу
 */
function updateCartQuantity(array &$cart, int $productId, int $change, int $stock)
{
    foreach ($cart as $key => &$item) {
        if ($item['id'] === $productId) {
            $newQuantity = $item['quantity'] + $change;
            if ($newQuantity > $stock) {
                throw new InsufficientStockException($productId, $stock, $newQuantity);
            }
            if ($newQuantity <= 0) {
                unset($cart[$key]);
                $cart = array_values($cart);
                logAction("Product ID $productId removed from cart due to quantity <= 0");
                return false;
            }
            $item['quantity'] = $newQuantity;
            logAction("Product ID $productId quantity updated to $newQuantity");
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
            logAction("Cart cleared by user");
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
                            logAction("Product ID $product_id quantity increased to $total_requested");
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
                        logAction("Product ID $product_id added to cart with quantity $quantity");
                    }
                    $_SESSION['success'] = "Товар додано до кошика.";
                    break;

                case 'remove':
                    foreach ($_SESSION['cart'] as $key => $item) {
                        if ($item['id'] === $product_id) {
                            unset($_SESSION['cart'][$key]);
                            $_SESSION['cart'] = array_values($_SESSION['cart']);
                            logAction("Product ID $product_id removed from cart");
                            $_SESSION['success'] = "Товар видалено з кошика.";
                            break;
                        }
                    }
                    break;

                case 'increase':
                    $result = updateCartQuantity($_SESSION['cart'], $product_id, 1, $product['stock']);
                    if ($result === false) {
                        throw new InsufficientStockException($product_id, $product['stock'], 1);
                    }
                    $_SESSION['success'] = "Кількість товару збільшена.";
                    break;

                case 'decrease':
                    $result = updateCartQuantity($_SESSION['cart'], $product_id, -1, $product['stock']);
                    if ($result === false) {
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
// Обчислення загальної вартості і кількості
$cart_items = array_values($_SESSION['cart']);

$total_price = 0;
$total_quantity = 0;
unset($item);
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
    $total_quantity += $item['quantity'];
}
$_SESSION['total_price'] = $total_price;
$_SESSION['total_quantity'] = $total_quantity;
header("Location: $redirectUrl");
exit;

