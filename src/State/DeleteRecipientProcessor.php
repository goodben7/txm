<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Manager\RecipientManager;

class DeleteRecipientProcessor implements ProcessorInterface
{
    public function __construct(private RecipientManager $manager)
    {   
    }

    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->delete($uriVariables['id']);
    }
}