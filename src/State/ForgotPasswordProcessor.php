<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ForgotPassword;
use App\Dto\ForgotPasswordDto;
use App\Manager\OTPManager;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class ForgotPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private OTPManager     $otpManager
    )
    {
    }

    /**
     * Summary of process
     * @param ForgotPasswordDto $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return ForgotPassword
     * @throws BadRequestHttpException
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ForgotPassword
    {
        $identifier = $data->identifier;
        $type = $data->identifierType;

        if (empty($identifier))
            throw new BadRequestHttpException('Email or phone number is required');

        if ($type === 'email')
            $user = $this->userRepository->findOneBy(['email' => $identifier]);
        else if ($type === 'phone')
            $user = $this->userRepository->findOneBy(['phone' => $identifier]);
        else
            throw new BadRequestHttpException('Invalid identifier type');

        if (!$user)
            throw new BadRequestHttpException('User not found');

        if ($user)
            $this->otpManager->sendPasswordResetOTP($user, $type);

        return new ForgotPassword();
    }
}
