<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ValidateStoreDto
{
    public function __construct(

        #[Assert\NotNull()]
        public bool $isVerified = false,
    )
    {  
    }
}