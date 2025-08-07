<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\VerifyOtp;
use App\Dto\VerifyOtpDto;
use App\Entity\OTP;
use App\Exception\InvalidActionInputException;
use App\Manager\OTPManager;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class VerifyOtpProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private OTPManager     $otpManager
    )
    {
    }

    /**
     * Process the OTP verification
     *
     * @param VerifyOtpDto $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return VerifyOtp
     * @throws BadRequestHttpException
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): VerifyOtp
    {
        $identifier = $data->identifier;
        $type = $data->identifierType;
        $code = $data->code;
        $otpType = $data->otpType;

        // Map the otpType string to the actual OTP type constant
        $otpTypeConstant = $this->mapOtpType($otpType);

        if (empty($identifier) || empty($code))
            throw new BadRequestHttpException('Identifier and verification code are required');

        if ($type === 'email')
            $user = $this->userRepository->findOneBy(['email' => $identifier]);
        else if ($type === 'phone')
            $user = $this->userRepository->findOneBy(['phone' => $identifier]);
        else
            throw new BadRequestHttpException('Invalid identifier type');

        if (!$user)
            throw new BadRequestHttpException('User not found');

        try {
            $verified = $this->otpManager->verifyOTP($user, $otpTypeConstant, $code);

            $response = new VerifyOtp();
            $response->verified = $verified;

            if (!$verified) {
                $response->message = 'Invalid or expired verification code';
            }

            return $response;
        } catch (InvalidActionInputException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    /**
     * Map the OTP type string to the corresponding constant
     *
     * @param string $otpType
     * @return string
     * @throws BadRequestHttpException
     */
    private function mapOtpType(string $otpType): string
    {
        return match ($otpType) {
            'password_reset' => OTP::TYPE_PASSWORD_RESET,
            'registration' => OTP::TYPE_REGISTRATION,
            'login' => OTP::TYPE_LOGIN,
            'email_change' => OTP::TYPE_EMAIL_CHANGE,
            'phone_change' => OTP::TYPE_PHONE_CHANGE,
            default => throw new BadRequestHttpException('Invalid OTP type')
        };
    }
}
