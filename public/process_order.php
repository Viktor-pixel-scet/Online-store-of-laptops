<?php

use DTO\OrderDTO;
use Repositories\CustomerRepository;
use Repositories\OrderRepository;
use Repositories\ProductRepository;
use Services\OrderService;

session_start();
require_once '../backend/database/Database.php';

$db = new Database();

$pdo = $db->getConnection();

require_once '../backend/dto/OrderDTO.php';
require_once '../backend/repositories/CustomerRepository.php';
require_once '../backend/repositories/OrderRepository.php';
require_once '../backend/repositories/ProductRepository.php';
require_once '../backend/services/OrderService.php';

if (!isset($_SESSION['cart']) || !isset($_SESSION['order'])) {
    header('Location: cart.php');
    exit;
}

$orderData = $_SESSION['order'];

$orderDTO = new OrderDTO(
    $orderData['customer'],
    $orderData['items'],
    $orderData['total_price']
);

$service = new OrderService(
    $pdo,
    new CustomerRepository($pdo),
    new OrderRepository($pdo),
    new ProductRepository($pdo)
);

try {
    $orderNumber = $service->process($orderDTO);
    $_SESSION['order_number'] = $orderNumber;
    $_SESSION['cart'] = [];
    header('Location: order_confirmation.php');
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = 'Виникла помилка при обробці замовлення. Будь ласка, спробуйте ще раз.';
    header('Location: checkout.php');
    exit;
}
