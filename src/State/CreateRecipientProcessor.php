<?php

namespace App\State;

use App\Model\NewRecipientModel;
use App\Manager\RecipientManager;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

class CreateRecipientProcessor implements ProcessorInterface
{
    public function __construct(private RecipientManager $manager)
    {
        
    }

    /**
     * @param \App\Dto\CreateRecipientDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $model = new NewRecipientModel(
            $data->customer, 
            $data->fullname,
            $data->addresses, 
            $data->phone, 
            $data->phone2, 
            $data->email,
            $data->recipientType
        );
 
        return $this->manager->createFrom($model); 
    }
}
