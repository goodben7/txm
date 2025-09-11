<?php

namespace App\Message\Command;

class CreateAuthUserCommand implements CommandInterface 
{
    public function __construct(
        public ?string $phone = null,
        public ?string $code = null,
        public ?string $personType = null
    ) {}
}