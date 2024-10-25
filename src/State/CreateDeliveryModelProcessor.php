<?php

namespace App\State;

use App\Model\CreateDeliveryModel;
use ApiPlatform\Metadata\Operation;
use App\Manager\DeliveryModelManager;
use ApiPlatform\State\ProcessorInterface;
use App\Message\Command\CommandBusInterface;
use App\Message\Command\CreateDeliveryCommand;

class CreateDeliveryModelProcessor implements ProcessorInterface
{
    public function __construct(private DeliveryModelManager $manager, private CommandBusInterface $bus,)
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
        
 
        $m = $this->manager->createFrom($model); 

        $this->bus->dispatch(new CreateDeliveryCommand($m));
        
        return $m;
    }
}
