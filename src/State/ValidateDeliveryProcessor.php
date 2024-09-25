<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Manager\DeliveryManager;

class ValidateDeliveryProcessor implements ProcessorInterface
{
    public function __construct(private DeliveryManager $manager)
    {   
    }

    /**
     * @param \App\Dto\ValidateDeliveryDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->validate($data->delivery);
    }
}