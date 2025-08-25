<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Manager\OrderManager;

class ValidateOrderProcessor implements ProcessorInterface
{
    public function __construct(private OrderManager $manager)
    {   
    }

    /**
     * @param \App\Dto\ValidateOrderDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->validate($data->order);
    }
}