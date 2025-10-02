<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class OrderItemOptionDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?int $productOptionValueId = null
    )
    {
    }
}