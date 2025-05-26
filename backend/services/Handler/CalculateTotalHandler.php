<?php

namespace Services\Handlers;
use DTO\OrderDTO;
use Services\Handlers\AbstractOrderHandler;

class CalculateTotalHandler extends AbstractOrderHandler
{
    protected function process(OrderDTO $orderDTO, array &$context): void
    {
        $total = 0;
        foreach ($orderDTO->items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        $context['total_price'] = $total;
    }
}
