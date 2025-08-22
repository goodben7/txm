<?php

namespace App\Dto;

use App\Entity\Order;
use Symfony\Component\Validator\Constraints as Assert;

class RejectOrderDto
{
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public Order $order;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public String $rejectionReason;
}