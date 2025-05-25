<?php

namespace Services\Handlers;

use DTO\OrderDTO;

abstract class AbstractOrderHandler
{
    public function handle(OrderDTO $orderDTO, array &$context): void
    {
        $this->validate($orderDTO, $context);
        $this->process($orderDTO, $context);
    }

    protected function validate(OrderDTO $orderDTO, array &$context): void
    {
    }

    abstract protected function process(OrderDTO $orderDTO, array &$context): void;

    protected function log(string $message): void
    {
        error_log("[OrderHandler] " . $message);
    }

}
