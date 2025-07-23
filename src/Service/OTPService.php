<?php

namespace App\Service;

use App\Entity\OTP;
use App\Entity\User;
use App\Repository\OTPRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class OTPService
{
    public function __construct(
        private EntityManagerInterface $em,
        private OTPRepository $otpRepository,
    )
    {
    }

    public function generate(User $user, string $type, int $expiryMinutes = 15): OTP
    {
        $this->invalidateExisting($user, $type);

        $otp = (new OTP())
            ->setUser($user)
            ->setType($type)
            ->setCode($this->generateCode())
            ->setExpiryDate(new \DateTimeImmutable("+{$expiryMinutes} minutes"))
            ->setSend(false);

        $this->em->persist($otp);
        $this->em->flush();

        return $otp;
    }

    public function verify(User $user, string $type, string $code): bool
    {
        $otp = $this->otpRepository->findValidOTP($user, $type, $code);
        if (!$otp)
            return false;

        $this->em->remove($otp);
        $this->em->flush();

        return true;
    }


    private function invalidateExisting(User $user, string $type): void
    {
        $existingOTPs = $this->otpRepository->findBy([
            'user' => $user,
            'type' => $type
        ]);

        foreach ($existingOTPs as $existingOTP)
            $this->em->remove($existingOTP);

        $this->em->flush();
    }

    private function generateCode(): string
    {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
