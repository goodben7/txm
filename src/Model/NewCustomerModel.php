<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewCustomerModel
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $companyName = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $fullname = null, 

        #[Assert\Valid()]
        /** @var array<\App\Entity\Address> */
        public array $addresses = [],

        public ?string $phone = null,

        public ?string $phone2 = null,

        #[Assert\Email]
        public ?string $email = null

    )
    {  
    }
}