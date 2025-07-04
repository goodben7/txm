<?php

namespace App\Dto;
use App\Entity\Profile;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUserDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        #[Assert\Email]
        public ?string $email = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $plainPassword = null, 

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Profile $profile = null,

        public ?string $phone = null,

        public ?string $displayName = null,

    )
    {  
    }
}