<?php

namespace App\Dto;

use App\Entity\Service;
use App\Entity\Customer;
use Symfony\Component\Validator\Constraints as Assert;

class CreateStoreDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $label = null,

        public ?string $description = null,

        public ?bool $active = true,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Customer $customer = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Service $service = null
    )
    {
    }
}