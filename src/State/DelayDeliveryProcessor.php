<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Manager\DeliveryManager;

class DelayDeliveryProcessor implements ProcessorInterface
{
    public function __construct(private DeliveryManager $manager)
    {   
    }

    /**
     * @param \App\Dto\DelayDeliveryDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->delay($data->delivery, $data->delayedAt, $data->message);
    }
}