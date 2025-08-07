<?php

namespace App\Dto;

use App\Entity\Document;
use Symfony\Component\Validator\Constraints as Assert;

class ValidateDocumentDto
{
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public Document $document;
}