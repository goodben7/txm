<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\VerifyOtpDto;
use App\State\VerifyOtpProcessor;
use App\Provider\VerifyOtpProvider;

#[ApiResource(
    shortName: 'VerifyOtp',
    operations: [
        new Post(
            uriTemplate: '/auth/verify-otp',
            input: VerifyOtpDto::class,
            output: false,
            provider: VerifyOtpProvider::class,
            processor: VerifyOtpProcessor::class
        )
    ]
)]
class VerifyOtp
{
    public string $message = 'OTP verification successful';
    public bool $verified = false;
}
