<?php

namespace App\State;


use ApiPlatform\Metadata\Operation;
use App\Model\NewDeliveryPersonModel;
use App\Manager\DeliveryPersonManager;
use ApiPlatform\State\ProcessorInterface;

class CreateDeliveryPersonProcessor implements ProcessorInterface
{
    public function __construct(private DeliveryPersonManager $manager)
    {
        
    }

    /**
     * @param \App\Dto\CreateDeliveryPersonDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $model = new NewDeliveryPersonModel(
            $data->fullname, 
            $data->phone, 
            $data->email, 
            $data->vehicleType, 
            $data->licenseNumber,
            $data->vehicleLicensePlate,
            $data->country,
            $data->address,
            $data->dateOfBirth,
            $data->city
        );
 
        return $this->manager->createDeliveryPerson($model); 
    }
}


