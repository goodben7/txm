<?php

namespace App\Dto;

use App\Entity\OrderItem;
use Symfony\Component\Validator\Constraints as Assert;

class OrderItemWithOptionsDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Valid()]
        public ?OrderItem $orderItem = null,
        
        /**
         * Array of selected product option value IDs
         * @var array<int>
         */
        public array $selectedOptionValueIds = []
    )
    {
    }
}