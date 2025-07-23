<?php

namespace App\Dto;

readonly class VerifyOtpDto
{
    public function __construct(
        public string $identifier,
        public string $identifierType,
        public string $code,
        public string $otpType
    )
    {
    }
}
