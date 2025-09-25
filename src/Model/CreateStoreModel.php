<?php

namespace App\Model;

use App\Entity\Address;
use App\Entity\Service;
use App\Entity\Customer;
use Symfony\Component\Validator\Constraints as Assert;

class CreateStoreModel
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $label = null,

        public ?string $description = null,

        #[Assert\Email]
        public ?string $email = null,

        #[Assert\Length(max: 15)]
        public ?string $phone = null,

        public ?bool $active = true,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Customer $customer = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Service $service = null,

        public ?Address $address = null,
    )
    {
    }
}