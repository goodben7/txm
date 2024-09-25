<?php

namespace App\Model;

use App\Entity\Zone;
use App\Entity\Customer;
use App\Entity\Delivery;
use App\Entity\Recipient;
use Symfony\Component\Validator\Constraints as Assert;

class NewDeliveryModel
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $pickupAddress = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $senderPhone = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $deliveryAddress = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $recipientPhone = null,

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
        public ?string $township = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Zone $zone = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Recipient $recipient = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Customer $customer = null,

    )
    {  
    }
}