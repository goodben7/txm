<?php

namespace App\Dto;

use App\Entity\Delivery;
use App\Entity\DeliveryPerson;
use Symfony\Component\Validator\Constraints as Assert;

class ReassignationDeliveryDto
{
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public Delivery $delivery;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public ?DeliveryPerson $deliveryPerson;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public String $message;
}