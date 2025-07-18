<?php

namespace App\Model;

use DateTimeImmutable;
use App\Entity\DeliveryPerson;
use Symfony\Component\Validator\Constraints as Assert;

class NewDeliveryPersonModel
{
    public function __construct(

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $fullname = null,

        public ?string $phone = null,
        
        public ?string $email = null,
    
        #[Assert\NotNull]
        #[Assert\NotBlank]
        #[Assert\Choice(choices: [DeliveryPerson::VEHICLE_TYPE_BICYCLE, DeliveryPerson::VEHICLE_TYPE_CAR, DeliveryPerson::VEHICLE_TYPE_MOTORCYCLE, DeliveryPerson::VEHICLE_TYPE_TRUCK, DeliveryPerson::VEHICLE_TYPE_OTHER])]
        public ?string $vehicleType = null,

        public ?string $licenseNumber = null,
        public ?string $vehicleLicensePlate = null,
 
        #[Assert\Country(alpha3: false)]
        public ?string $country = null,

        public ?string $address = null,

        public ?DateTimeImmutable $dateOfBirth = null,

        public ?string $city = null,

        public ?DateTimeImmutable $startDate = null,
        public ?DateTimeImmutable $endDate = null,
    ) {
    }
}
