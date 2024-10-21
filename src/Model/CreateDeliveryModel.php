<?php

namespace App\Model;

use App\Entity\DeliveryModel;
use Symfony\Component\Validator\Constraints as Assert;

class CreateDeliveryModel
{
    public function __construct(

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $fullname = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $phone = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        #[Assert\Choice(choices: [DeliveryModel::TYPE_PACKAGE, DeliveryModel::TYPE_MAIL])]
        public ?string $type = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $description = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $address = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?\DateTimeImmutable $deliveryDate = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $amount = null,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $numberMP = null,

        public ?string $data1 = null,
        public ?string $data2 = null,
        public ?string $data3 = null,
        public ?string $data4 = null,

    )
    {  
    }
}