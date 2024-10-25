<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Manager\DeliveryManager;
use App\Model\UpdateDeliveryModel;

class UpdateDeliveryProcessor implements ProcessorInterface
{
    public function __construct(private DeliveryManager $manager)
    {
        
    }

    /**
     * @param \App\Dto\CreateDeliveryDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $model = new UpdateDeliveryModel(
            $data->type, 
            $data->description, 
            $data->recipient, 
            $data->customer,
            $data->pickupAddress,
            $data->deliveryAddress,
            $data->additionalInformation

        );
 
        return $this->manager->updateFrom($model, $uriVariables['id']); 
    }
}
