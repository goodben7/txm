<?php

namespace App\Dto;

use App\Entity\Delivery;

use Symfony\Component\Validator\Constraints as Assert;

class DelayDeliveryDto
{
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public Delivery $delivery;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public \DateTimeImmutable $delayedAt;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public String $message;
}