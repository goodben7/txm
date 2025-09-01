<?php

namespace App\Dto;

use App\Entity\Order;
use App\Entity\Address;
use App\Entity\Delivery;
use Symfony\Component\Validator\Constraints as Assert;

class FinishOrderDto
{
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    public Order $order;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [Delivery::TYPE_PACKAGE, Delivery::TYPE_MAIL])]
    public ?string $type = null;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    public ?\DateTimeImmutable $deliveryDate = null;
    
    public ?Address $pickupAddress = null;
    public ?string $description = null;

    #[Assert\Choice(choices: [Delivery::CREATED_FROM_API, Delivery::CREATED_FROM_MOBILE_APP, Delivery::CREATED_FROM_WEB_APP])]
    public ?string $createdFrom = null;
}