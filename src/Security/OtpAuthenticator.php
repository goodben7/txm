<?php

namespace App\Security;

use App\Entity\User;
use App\Service\AuthService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class OtpAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private AuthService $authService,
        private ?\Psr\Log\LoggerInterface $logger = null
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/api/auth/verify-otp' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $data = json_decode($request->getContent(), true);
        $phone = $data['phone'] ?? null;
        $code = $data['code'] ?? null;

        if (!$phone || !$code) {
            throw new AuthenticationException('Phone number and OTP code are required');
        }

        return new SelfValidatingPassport(
            new UserBadge($phone, function ($userIdentifier) use ($phone, $code) {
                // Verify OTP and get or create user
                $user = $this->authService->verifyOtp($phone, $code);
                
                if (!$user) {
                    throw new AuthenticationException('Invalid OTP code');
                }
                
                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /**
         * @var User $user
         */
        $user = $token->getUser();
        
        // Log successful authentication
        if ($this->logger) {
            $this->logger->info('OTP authentication successful', [
                'user_id' => $user->getId(),
                'phone' => $user->getPhone(),
                'roles' => $user->getRoles(),
                'token_class' => get_class($token),
            ]);
        }
        
        return new JsonResponse([
            'success' => true,
            'user' => [
                'id' => $user->getId(),
                'phone' => $user->getPhone(),
                'displayName' => $user->getDisplayName(),
            ]
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'success' => false,
            'message' => $exception->getMessage()
        ], Response::HTTP_UNAUTHORIZED);
    }
}