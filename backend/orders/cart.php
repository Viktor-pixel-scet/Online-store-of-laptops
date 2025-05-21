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

if (isset($_GET['action']) && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new ProductNotFoundException($product_id);
        }

        if ($_GET['action'] === 'add') {
            $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

            if ($product['stock'] <= 0) {
                throw new ProductOutOfStockException($product_id);
            }
            
            if ($quantity > $product['stock']) {
                throw new InsufficientStockException($product_id, $product['stock'], $quantity);
            }

            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $product_id) {
                    $total_requested = $item['quantity'] + $quantity;

                    if ($total_requested <= $product['stock']) {
                        $item['quantity'] += $quantity;
                    } else {
                        throw new InsufficientStockException($product_id, $product['stock'], $total_requested);
                    }

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
        }

        if ($_GET['action'] === 'remove') {
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $product_id) {
                    unset($_SESSION['cart'][$key]);
                    break;
                }
            }

            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }

        if ($_GET['action'] === 'increase') {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $product_id) {
                    if ($item['quantity'] < $product['stock']) {
                        $item['quantity']++;
                    } else {
                        throw new InsufficientStockException($product_id, $product['stock'], $item['quantity'] + 1);
                    }
                    break;
                }
            }
        }

        if ($_GET['action'] === 'decrease') {
            foreach ($_SESSION['cart'] as $key => &$item) {
                if ($item['id'] == $product_id) {
                    $item['quantity']--;

                    if ($item['quantity'] <= 0) {
                        unset($_SESSION['cart'][$key]);
                        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                    }

                    break;
                }
            }
        }

        header('Location: cart.php');
        exit;
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
    } catch (Exception $e) {
        error_log('General error: ' . $e->getMessage());
        $_SESSION['error'] = "Виникла помилка при обробці вашого запиту.";
    }
    
    header('Location: cart.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['cart'] = [];

    header('Location: cart.php');
    exit;
}

$cart_items = array_values($_SESSION['cart']);

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>