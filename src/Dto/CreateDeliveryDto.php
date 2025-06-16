<?php

namespace App\Dto;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\Delivery;
use App\Entity\Recipient;
use Symfony\Component\Validator\Constraints as Assert;

class CreateDeliveryDto  {

    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        #[Assert\Choice(choices: [Delivery::TYPE_PACKAGE, Delivery::TYPE_MAIL])]
        public ?string $type = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $description = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?\DateTimeImmutable $deliveryDate = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Recipient $recipient = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Customer $customer = null,
        
        public ?Address $pickupAddress = null,

        public ?Address $deliveryAddress = null,

        public ?string $additionalInformation = null,
        
        #[Assert\Choice(choices: [Delivery::CREATED_FROM_API, Delivery::CREATED_FROM_MOBILE_APP, Delivery::CREATED_FROM_WEB_APP])]
        public ?string $createdFrom = null,

    )
    {  
    }

}