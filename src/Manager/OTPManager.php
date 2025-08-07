<?php

namespace App\Manager;

use App\Entity\Notification;
use App\Entity\OTP;
use App\Entity\User;
use App\Enum\NotificationType;
use App\Message\SendNotificationMessage;
use App\Service\OTPService;
use App\Exception\InvalidActionInputException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class OTPManager
{
    public function __construct(
        private OTPService             $otpService,
        private EntityManagerInterface $em,
        private MessageBusInterface    $bus
    )
    {
    }

    public function sendPasswordResetOTP(User $user, string $type = 'phone'): OTP
    {
        $otp = $this->otpService->generate($user, OTP::TYPE_PASSWORD_RESET);

        if ($type === 'email') {
            $data = [
                "identifier" => $user->getEmail(),
                "sentVia" => Notification::SENT_VIA_GMAIL
            ];
        } else {
            $data = [
                "identifier" => $user->getPhone(),
                "sentVia" => Notification::SENT_VIA_WHATSAPP
            ];
        }

        $notification = (new Notification())
            ->setSubject("Votre mot de passe a été réinitialisé")
            ->setBody(sprintf("Votre code de reinitialisation de mot de passe est : %s. Ce code expirera dans 15 minutes.", $otp->getCode()))
            ->setType(NotificationType::RESET_PASSWORD)
            ->setTargetType($type)
            ->setTarget($data['identifier'])
            ->setSentVia($data['sentVia']);

        $this->em->persist($notification);
        $this->em->flush();

        $this->bus->dispatch(new SendNotificationMessage($notification));

        return $otp;
    }

    /**
     * Verify an OTP
     */
    public function verifyOTP(User $user, string $type, string $code): bool
    {
        return $this->otpService->verify($user, $type, $code);
    }

    public function verifyRegistrationOTP(User $user, string $code): bool
    {
        if (!$this->verifyOTP($user, OTP::TYPE_REGISTRATION, $code))
            throw new InvalidActionInputException("the registration code is invalid or expired.");

        // Additional logic for completing registration could go here

        return true;
    }

    public function verifyPasswordResetOTP(User $user, string $code): bool
    {
        if (!$this->verifyOTP($user, OTP::TYPE_PASSWORD_RESET, $code))
            throw new InvalidActionInputException("the password reset code is invalid or expired.");

        return true;
    }
}
