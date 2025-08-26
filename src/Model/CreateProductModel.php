<?php

namespace App\Model;

use App\Entity\Store;
use Symfony\Component\Validator\Constraints as Assert;

class CreateProductModel
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $name = null,

        public ?string $description = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $price = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Store $store = null,

        public ?bool $active = true,

        public ?string $type = null,

        #[Assert\NotNull()]
        public bool $isVerified = false,

        #[Assert\Currency]
        public ?string $currency = null,
    )
    {
    }
}