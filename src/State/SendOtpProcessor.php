<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Dto\OtpRequest;
use App\Service\AuthService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SendOtpProcessor implements ProcessorInterface
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * @param OtpRequest $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $phone = $data->getPhone();

        if (!$phone) {
            throw new BadRequestHttpException('Phone number is required');
        }

        try {
            $this->authService->sendOtp($phone);
            return ['success' => true, 'message' => 'OTP sent successfully'];
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}