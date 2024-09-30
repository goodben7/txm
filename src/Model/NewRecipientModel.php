<?php

namespace App\Model;

use App\Entity\Customer;
use Symfony\Component\Validator\Constraints as Assert;

class NewRecipientModel
{
    public function __construct(
        
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Customer $customer = null, 

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $fullname = null, 

        #[Assert\Count(min: 1)]
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