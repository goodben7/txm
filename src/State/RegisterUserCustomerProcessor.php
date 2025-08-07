<?php

namespace App\State;

use App\Manager\UserManager;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Model\NewRegisterUserCustomerModel;

class RegisterUserCustomerProcessor implements ProcessorInterface
{
    public function __construct(private UserManager $manager)
    {
        
    }

    /**
     * @param \App\Dto\NewRegisterUserCustomerDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $model = new NewRegisterUserCustomerModel(
            $data->companyName, 
            $data->fullname,
            $data->addresses, 
            $data->phone, 
            $data->phone2, 
            $data->email,
            $data->plainPassword,
        );
 
        return $this->manager->registerCustomer($model);
    }
}