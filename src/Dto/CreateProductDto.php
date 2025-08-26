<?php

namespace App\Dto;

use App\Entity\Store;
use App\Enum\ProductType;
use Symfony\Component\Validator\Constraints as Assert;

class CreateProductDto
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

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?bool $active = true,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        #[Assert\Choice(callback: [ProductType::class, 'getAll'], message: 'Invalid product type.')]
        public ?string $type = null,

        #[Assert\NotNull()]
        public bool $isVerified = false,

        #[Assert\Currency]
        public ?string $currency = null,
    )
    {
    }
}