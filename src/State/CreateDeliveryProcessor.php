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
            $data->type, 
            $data->description, 
            $data->deliveryDate, 
            $data->recipient, 
            $data->customer,
            $data->pickupAddress,
            $data->deliveryAddress,
            $data->additionalInformation,
            $data->createdFrom

        );
 
        return $this->manager->createFrom($model); 
    }
}
