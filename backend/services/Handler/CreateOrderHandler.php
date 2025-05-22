<?php

namespace Services\Handlers;

use DTO\OrderDTO;
use Repositories\OrderRepository;

class CreateOrderHandler extends AbstractOrderHandler
{
    private OrderRepository $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    protected function process(OrderDTO $orderDTO, array &$context): void
    {
        $orderId = $this->orderRepo->create(
            $context['order_number'],
            $context['customer_id'],
            $orderDTO->totalPrice,
            $orderDTO->customer['payment_method']
        );
        $context['order_id'] = $orderId;
    }
}
