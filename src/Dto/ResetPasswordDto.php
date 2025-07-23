<?php

namespace App\Dto;

readonly class ResetPasswordDto
{
    public function __construct(
        public string $identifier,
        public string $identifierType,
        public string $newPassword
    )
    {
    }
}
