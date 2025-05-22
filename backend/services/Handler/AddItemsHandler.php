<?php

namespace Services\Handlers;

use DTO\OrderDTO;
use Repositories\OrderRepository;

class AddItemsHandler extends AbstractOrderHandler
{
    private OrderRepository $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    protected function process(OrderDTO $orderDTO, array &$context): void
    {
        foreach ($orderDTO->items as $item) {
            $this->orderRepo->addItem(
                $context['order_id'],
                $item['id'],
                $item['quantity'],
                $item['price']
            );
        }
    }
}
