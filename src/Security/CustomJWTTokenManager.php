<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class CustomJWTTokenManager implements JWTTokenManagerInterface
{
    private const int OTP_TOKEN_TTL = 15552000; // 6 months in seconds (180 days * 24 hours * 60 minutes * 60 seconds)
    
    public function __construct(
        private JWTTokenManagerInterface $innerJwtManager
    ) {}
    
    public function create(UserInterface $user): string
    {
        // Get the calling class from the debug backtrace
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1]['class'] ?? null;
        
        // If the token is created from OtpAuthenticator, set a custom TTL
        if ($caller === OtpAuthenticator::class) {
            // If the inner manager is a JWTManager, we can set the TTL directly
            if ($this->innerJwtManager instanceof JWTManager) {
                return $this->innerJwtManager->createFromPayload($user, ['exp' => time() + self::OTP_TOKEN_TTL]);
            }
        }
        
        // Otherwise, use the default TTL
        return $this->innerJwtManager->create($user);
    }
    
    
    public function decode(TokenInterface $token): array|bool
    {
        return $this->innerJwtManager->decode($token);
    }
    
    public function parse(string $token): array
    {
        return $this->innerJwtManager->parse($token);
    }
    
    public function createFromPayload(UserInterface $user, array $payload = []): string
    {
        return $this->innerJwtManager->createFromPayload($user, $payload);
    }

    public function getUserIdClaim(): string
    {
        if (method_exists($this->innerJwtManager, 'getUserIdClaim')) {
            return $this->innerJwtManager->getUserIdClaim();
        }
        
        return 'username';
    }
}