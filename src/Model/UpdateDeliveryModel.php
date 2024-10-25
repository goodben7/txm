<?php

namespace App\Model;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\Delivery;
use App\Entity\Recipient;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateDeliveryModel
{
    public function __construct(
       
        #[Assert\Choice(choices: [Delivery::TYPE_PACKAGE, Delivery::TYPE_MAIL])]
        public ?string $type = null,

        public ?string $description = null,

        public ?Recipient $recipient = null,

        public ?Customer $customer = null,
        
        public ?Address $pickupAddress = null,

        public ?Address $deliveryAddress = null,

        public ?string $additionalInformation = null,

    )
    {  
    }
}