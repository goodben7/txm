<?php

namespace App\Dto;

use App\Entity\Document;
use Symfony\Component\Validator\Constraints as Assert;

class RejectDocumentDto
{
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public Document $document;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public String $rejectionReason;
}