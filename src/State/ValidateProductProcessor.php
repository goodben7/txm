<?php

namespace App\State;

use App\Manager\ProductManager;
use ApiPlatform\State\ProcessorInterface;

class ValidateProductProcessor implements ProcessorInterface
{
    public function __construct(private ProductManager $manager)
    {   
    }

     /**
     * @param \App\Dto\ValidateProductDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->validate($uriVariables['id'], $data->isVerified);
    }
}