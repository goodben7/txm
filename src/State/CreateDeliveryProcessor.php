<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Manager\DeliveryManager;
use App\Model\NewDeliveryModel;

class CreateDeliveryProcessor implements ProcessorInterface
{
    public function __construct(private DeliveryManager $manager)
    {
        
    }

    /**
     * @param \App\Dto\CreateDeliveryDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $model = new NewDeliveryModel(
            $data->pickupAddress, 
            $data->senderPhone, 
            $data->deliveryAddress, 
            $data->recipientPhone, 
            $data->type, 
            $data->description, 
            $data->deliveryDate, 
            $data->township, 
            $data->zone, 
            $data->recipient, 
            $data->customer
        );
 
        return $this->manager->createFrom($model); 
    }
}
