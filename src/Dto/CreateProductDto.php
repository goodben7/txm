<?php

namespace App\Dto;

use App\Entity\Store;
use App\Entity\ProductType;
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
        public ?ProductType $type = null,

        #[Assert\NotNull()]
        public bool $isVerified = false,

        #[Assert\Currency]
        public ?string $currency = null,
        
        /**
         * Array of product options with their values
         * Format: [
         *   ['name' => 'Color', 'values' => [
         *     ['value' => 'Red', 'priceAdjustment' => '0.00'],
         *     ['value' => 'Blue', 'priceAdjustment' => '5.00']
         *   ]],
         *   ['name' => 'Size', 'values' => [
         *     ['value' => 'S', 'priceAdjustment' => '0.00'],
         *     ['value' => 'M', 'priceAdjustment' => '10.00']
         *   ]]
         * ]
         */
        public ?array $productOptions = [],
    )
    {
    }
}