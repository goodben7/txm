<?php

namespace App\Dto;

use App\Entity\Order;
use Symfony\Component\Validator\Constraints as Assert;

class InprogressOrderDto
{
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public Order $order;
}