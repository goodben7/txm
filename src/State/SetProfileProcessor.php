<?php

namespace App\State;

use App\Manager\UserManager;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

class SetProfileProcessor implements ProcessorInterface
{
    public function __construct(private UserManager $manager)
    {
        
    }

    /** @param \App\Dto\SetUserProfileDto $data */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        return $this->manager->setUserProfile($uriVariables['id'], $data->profile);
    }
}
