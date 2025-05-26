<?php

namespace Services;

use DTO\OrderDTO;
use Repositories\CustomerRepository;
use Repositories\OrderRepository;
use Repositories\ProductRepository;
use PDO;
use Services\Handlers\CalculateTotalHandler;
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
        PDO                $db,
        CustomerRepository $customerRepo,
        OrderRepository    $orderRepo,
        ProductRepository  $productRepo
    )
    {
        $this->db = $db;
        $this->customerRepo = $customerRepo;
        $this->orderRepo = $orderRepo;
        $this->productRepo = $productRepo;
    }

    public function process(OrderDTO $orderDTO): string
    {
        $context = [];

        // тепер збираємо ланцюжок динамічно з масиву налаштувань
        $handlers = [
            GenerateOrderNumberHandler::class => [],
            CreateCustomerHandler::class => [$this->customerRepo],
            CreateOrderHandler::class => [$this->orderRepo],
            CalculateTotalHandler::class => [],
            AddItemsHandler::class => [$this->orderRepo],
            ReduceStockHandler::class => [$this->productRepo],
        ];

        $first = null;
        $prev = null;
        foreach ($handlers as $class => $deps) {
            /** @var \Services\Handlers\AbstractOrderHandler $h */
            $h = new $class(...$deps);
            if ($first === null) {
                $first = $h;
            }
            if ($prev !== null) {
                $prev->setNext($h);
            }
            $prev = $h;
        }

        try {
            $this->db->beginTransaction();

            // Запускаємо обробку ланцюжка
            $first->handle($orderDTO, $context);

            $this->db->commit();

            return $context['order_number'];

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Order processing error: ' . $e->getMessage());
            throw new \Exception('Order processing failed.');
        }
    }
}
