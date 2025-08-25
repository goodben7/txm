<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Manager\StoreManager;

class ValidateStoreProcessor implements ProcessorInterface
{
    public function __construct(private StoreManager $manager)
    {   
    }

     /**
     * @param \App\Dto\ValidateStoreDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->validate($uriVariables['id'], $data->isVerified);
    }
}