<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;
use App\Manager\DocumentManager;

class RejectDocumentProcessor implements ProcessorInterface
{
    public function __construct(private DocumentManager $manager)
    {   
    }

    /**
     * @param \App\Dto\RejectDocumentDto $data 
     */
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->manager->reject($data->document, $data->rejectionReason);
    }
}