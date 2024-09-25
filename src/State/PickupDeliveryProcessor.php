<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Manager\DeliveryManager;

class PickupDeliveryProcessor implements ProcessorInterface
{
    public function __construct(private DeliveryManager $manager)
    {   
    }

    /**
     * @param \App\Dto\PickupDeliveryDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->pickup($data->delivery);
    }
}