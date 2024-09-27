<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewZOneModel
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $label = null,

        public ?string $description = null, 

        public ?bool $actived = null,

        #[Assert\Valid()]
        /** @var array<\App\Entity\Township> */
        public array $townships = [],
        
    )
    {  
    }
}