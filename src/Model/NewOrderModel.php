<?php

namespace App\Model;

use App\Entity\Address;
use Symfony\Component\Validator\Constraints as Assert;

class NewOrderModel
{
    public function __construct(

        #[Assert\Count(min: 1)]
        #[Assert\Valid()]
        /** @var array<\App\Entity\OrderItem> */
        public array $orderItems = [],

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $userId = null,

        public ?Address $deliveryAddress = null,

        public ?string $description = null,

        public ?Address $pickupAddress = null,
        
        /**
         * Array of selected product options for each order item
         * Format: [
         *   'orderItemIndex' => [
         *     productOptionValueId1,
         *     productOptionValueId2
         *   ]
         * ]
         * Where orderItemIndex corresponds to the index in the orderItems array
         */
        public array $selectedOptions = [],
    )
    {
    }
}