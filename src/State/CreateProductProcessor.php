<?php

namespace App\State;

use App\Manager\ProductManager;
use App\Model\CreateProductModel;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

class CreateProductProcessor implements ProcessorInterface
{
    public function __construct(private ProductManager $manager)
    {
        
    }

    /**
     * @param \App\Dto\CreateProductDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $model = new CreateProductModel(
            $data->name, 
            $data->description,
            $data->price, 
            $data->store, 
            $data->active, 
            $data->type,
            $data->isVerified
        );
 
        return $this->manager->createFrom($model); 
    }
}