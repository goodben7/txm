<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Manager\DeliveryModelManager;
use App\Model\CreateDeliveryModel;

class CreateDeliveryModelProcessor implements ProcessorInterface
{
    public function __construct(private DeliveryModelManager $manager)
    {
        
    }

    /**
     * @param \App\Dto\CreateDeliveryModelDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $model = new CreateDeliveryModel(
            $data->fullname, 
            $data->phone, 
            $data->type,
            $data->description,
            $data->address,
            $data->deliveryDate,
            $data->amount,
            $data->numberMP,
            $data->data1,
            $data->data2,
            $data->data3,
            $data->data4,
        );
 
        return $this->manager->createFrom($model); 
    }
}
