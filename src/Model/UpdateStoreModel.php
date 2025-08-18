<?php

namespace App\Model;

use App\Entity\Service;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateStoreModel
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $label = null,

        public ?string $description  = null,

        #[Assert\Email]
        public ?string $email = null,

        #[Assert\Length(max: 15)]
        public ?string $phone = null,

        public ?bool $active = true,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?Service $service = null
    )
    {
    }
}