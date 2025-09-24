<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Message\Command\CommandBusInterface;
use App\Message\Command\CreateStoreCommand;

class CreateStoreProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $bus, 
    )
    {  
    }

    /**
     * @param \App\Dto\CreateStoreDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->bus->dispatch(new CreateStoreCommand(
            $data->label,
            $data->description,
            null,
            null,
            $data->active,
            $data->customer,
            $data->service,
            $data->addresses,
        ));

    }
}
