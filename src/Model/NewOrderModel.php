<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class NewOrderModel
{
    public function __construct(

        #[Assert\Count(min: 1)]
        #[Assert\Valid()]
        /** @var array<\App\Entity\OrderItem> */
        public array $orderItems = [],

    )
    {
    }
}