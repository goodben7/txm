<?php

namespace App\State;

use App\ApiResource\ResetPassword;
use App\Dto\ResetPasswordDto;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProcessorInterface;
use App\Exception\InvalidActionInputException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class ResetPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepository              $userRepository,
        private EntityManagerInterface      $em,
        private UserPasswordHasherInterface $passwordHasher,
        private MessageBusInterface         $bus
    )
    {
    }

    /**
     * Summary of process
     * @param ResetPasswordDto $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return ResetPassword
     * @throws ExceptionInterface
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ResetPassword
    {
        $identifier = $data->identifier;
        $type = $data->identifierType;
        $newPassword = $data->newPassword;

        if (empty($identifier) || empty($newPassword))
            throw new BadRequestHttpException('Identifier and new password are required');

        if ($type === 'email')
            $user = $this->userRepository->findOneBy(['email' => $identifier]);
        else if ($type === 'phone')
            $user = $this->userRepository->findOneBy(['phone' => $identifier]);
        else
            throw new BadRequestHttpException('Invalid identifier type');

        if (!$user)
            throw new BadRequestHttpException('Invalid credentials');

        try {
            $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
            $this->em->flush();

            return new ResetPassword();
        } catch (InvalidActionInputException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
