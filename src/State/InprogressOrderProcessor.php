<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Manager\OrderManager;

class InprogressOrderProcessor implements ProcessorInterface
{
    public function __construct(private OrderManager $manager)
    {   
    }

    /**
     * @param \App\Dto\InprogressOrderDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->inprogress($data->order);
    }
}