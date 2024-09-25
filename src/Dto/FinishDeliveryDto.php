<?php

namespace App\Dto;

use App\Entity\Delivery;

use Symfony\Component\Validator\Constraints as Assert;

class FinishDeliveryDto
{
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public Delivery $delivery;
}