<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Manager\ZoneManager;
use App\Model\NewZOneModel;

class CreateZoneProcessor implements ProcessorInterface
{
    public function __construct(private ZoneManager $manager)
    {
        
    }

    /**
     * @param \App\Dto\CreateZoneDto $data 
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        $model = new NewZOneModel(
            $data->label, 
            $data->description,
            $data->actived, 
            $data->townships
        );
 
        return $this->manager->createFrom($model); 
    }
}
