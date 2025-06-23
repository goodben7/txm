<?php

namespace App\State;

use App\Manager\DeliveryManager;
use ApiPlatform\State\ProcessorInterface;

class ReassignationDeliveryProcessor implements ProcessorInterface
{
    public function __construct(private DeliveryManager $manager)
    {
    }

    /**
     * @param \App\Dto\ReassignationDeliveryDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->reassigne($data->delivery, $data->deliveryPerson, $data->message);
    }


    
}