<?php declare(strict_types=1);

namespace Services\Handlers;

use DTO\OrderDTO;

abstract class AbstractOrderHandler
{
    private ?AbstractOrderHandler $next = null;

    public function setNext(AbstractOrderHandler $handler): AbstractOrderHandler
    {
        $this->next = $handler;
        return $handler;
    }

    public function handle(OrderDTO $orderDTO, array &$context): void
    {
        $this->log('Start handler: ' . static::class);
        $this->validate($orderDTO, $context);
        $this->process($orderDTO, $context);
        $this->log('End handler: ' . static::class);

        if ($this->next !== null) {
            $this->next->handle($orderDTO, $context);
        }
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
