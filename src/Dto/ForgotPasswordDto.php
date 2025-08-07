<?php

namespace App\Dto;

readonly class ForgotPasswordDto
{
    public function __construct(
        public ?string $identifier = null,
        public ?string $identifierType = null,
    )
    {
    }
}
