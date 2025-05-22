<?php

namespace Services\Handlers;

use DTO\OrderDTO;

class GenerateOrderNumberHandler extends AbstractOrderHandler
{
    protected function process(OrderDTO $orderDTO, array &$context): void
    {
        $context['order_number'] = 'ORDER-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
    }
}