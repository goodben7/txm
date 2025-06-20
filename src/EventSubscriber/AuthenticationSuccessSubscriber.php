<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Exception\UserAuthenticationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_authentication_success' => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);


        if ($user->isDeleted()) {
            
            throw new UserAuthenticationException('This user is not active. Please contact support.');
        }
    }
}
