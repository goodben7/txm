<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Manager\OrderManager;

class FinishOrderProcessor implements ProcessorInterface
{
    public function __construct(private OrderManager $manager)
    {   
    }

    /**
     * @param \App\Dto\FinishOrderDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->finish(
            $data->order,
            $data->type,
            $data->deliveryDate,
            $data->pickupAddress,
            $data->description,
            $data->createdFrom
        );
    }
}