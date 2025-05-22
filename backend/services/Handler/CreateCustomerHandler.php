<?php

namespace Services\Handlers;

use DTO\OrderDTO;
use Repositories\CustomerRepository;

class CreateCustomerHandler extends AbstractOrderHandler
{
    private CustomerRepository $customerRepo;

    public function __construct(CustomerRepository $customerRepo)
    {
        $this->customerRepo = $customerRepo;
    }

    protected function process(OrderDTO $orderDTO, array &$context): void
    {
        $customerId = $this->customerRepo->create($orderDTO->customer);
        $context['customer_id'] = $customerId;
    }
}