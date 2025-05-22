<?php

namespace Services;

use DTO\OrderDTO;
use Repositories\CustomerRepository;
use Repositories\OrderRepository;
use Repositories\ProductRepository;
use PDO;
use Services\Handlers\GenerateOrderNumberHandler;
use Services\Handlers\CreateCustomerHandler;
use Services\Handlers\CreateOrderHandler;
use Services\Handlers\AddItemsHandler;
use Services\Handlers\ReduceStockHandler;

class OrderService
{
    private PDO $db;
    private CustomerRepository $customerRepo;
    private OrderRepository $orderRepo;
    private ProductRepository $productRepo;

    public function __construct(
        PDO $db,
        CustomerRepository $customerRepo,
        OrderRepository $orderRepo,
        ProductRepository $productRepo
    ) {
        $this->db = $db;
        $this->customerRepo = $customerRepo;
        $this->orderRepo = $orderRepo;
        $this->productRepo = $productRepo;
    }

    public function process(OrderDTO $orderDTO): string
    {
        $context = [];

        // Створюємо обробники і формуємо ланцюжок
        $generate = new GenerateOrderNumberHandler();
        $createCustomer = new CreateCustomerHandler($this->customerRepo);
        $createOrder = new CreateOrderHandler($this->orderRepo);
        $addItems = new AddItemsHandler($this->orderRepo);
        $reduceStock = new ReduceStockHandler($this->productRepo);

        $generate->setNext($createCustomer)
            ->setNext($createOrder)
            ->setNext($addItems)
            ->setNext($reduceStock);

        try {
            $this->db->beginTransaction();

            // Запускаємо обробку ланцюжка
            $generate->handle($orderDTO, $context);

            $this->db->commit();

            return $context['order_number'];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Order processing error: ' . $e->getMessage());
            throw new \Exception('Order processing failed.');
        }
    }
}
