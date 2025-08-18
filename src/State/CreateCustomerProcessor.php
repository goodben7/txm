<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Manager\CustomerManager;
use App\Model\NewCustomerModel;

class CreateCustomerProcessor implements ProcessorInterface
{
    public function __construct(private CustomerManager $manager)
    {
        
    }

    /**
     * @param \App\Dto\CreateCustomerDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $model = new NewCustomerModel(
            $data->companyName, 
            $data->fullname,
            $data->addresses, 
            $data->phone, 
            $data->phone2, 
            $data->email,
            $data->isPartner,
        );
 
        return $this->manager->createFrom($model); 
    }
}
