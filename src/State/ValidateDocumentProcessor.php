<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Manager\DocumentManager;

class ValidateDocumentProcessor implements ProcessorInterface
{
    public function __construct(private DocumentManager $manager)
    {   
    }

    /**
     * @param \App\Dto\ValidateDocumentDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->validate($data->document);
    }
}