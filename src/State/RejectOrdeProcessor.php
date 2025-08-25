<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Manager\OrderManager;

class RejectOrdeProcessor implements ProcessorInterface
{
    public function __construct(private OrderManager $manager)
    {   
    }

    /**
     * @param \App\Dto\RejectOrderDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->reject($data->order, $data->rejectionReason);
    }
}