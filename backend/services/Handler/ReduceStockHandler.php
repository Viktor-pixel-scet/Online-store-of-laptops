<?php

namespace Services\Handlers;

use DTO\OrderDTO;
use Repositories\ProductRepository;

class ReduceStockHandler extends AbstractOrderHandler
{
    private ProductRepository $productRepo;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    protected function process(OrderDTO $orderDTO, array &$context): void
    {
        foreach ($orderDTO->items as $item) {
            $this->productRepo->reduceStock($item['id'], $item['quantity']);
        }
    }
}
