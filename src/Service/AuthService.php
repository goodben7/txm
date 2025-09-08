<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\AuthSession;
use App\Event\OtpSentEvent;
use App\Model\UserProxyIntertace;
use App\Repository\UserRepository;
use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AuthSessionRepository;
use App\Exception\UnavailableDataException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $em,
        private AuthSessionRepository $authSessionRepo,
        private UserRepository $userRepository,
        private ProfileRepository $profileRepository,
        private EventDispatcherInterface $eventDispatcher,
        private CodeGeneratorService $codeGeneratorService
    ) {
    }

    public function sendOtp(string $phone): void
    {
        $otp = (string) random_int(1000, 9999);
        $session = new AuthSession();
        $session->setPhone($phone);
        $session->setOtpCode($otp);
        $session->setCreatedAt(new \DateTimeImmutable());
        $session->setExpiresAt((new \DateTimeImmutable())->modify('+5 minutes'));
        $session->setIsValidated(false);

        $this->em->persist($session);
        $this->em->flush();
        
        // Dispatch event after OTP is sent
        $event = new OtpSentEvent($session);
        $this->eventDispatcher->dispatch($event, OtpSentEvent::NAME);
    }

    public function verifyOtp(string $phone, string $code): ?User
    {
        try {
            $session = $this->authSessionRepo->findValidSession($phone, $code);

            if (!$session) return null;

            $session->setIsValidated(true);
            $user = $this->userRepository->findOneBy(['phone' => $phone]);

            if (!$user) {
                try {
                    $profile = $this->profileRepository->findOneBy(['personType' => UserProxyIntertace::PERSON_CUSTOMER]);

                    if (null === $profile) {
                        throw new UnavailableDataException('cannot find profile with person type: customer');
                    }

                    $code = $this->codeGeneratorService->generateCode('Recipient', UserProxyIntertace::PERSON_CUSTOMER);
                        
                    if ($this->codeGeneratorService->codeExists($code)) {
                        throw new UnavailableDataException('code already exists');
                    }

                    $user = new User();
                    $user->setPhone($phone);
                    $user->setPassword(null); // Définir le mot de passe à null pour les utilisateurs authentifiés par OTP
                    $user->setDeleted(false);
                    $user->setProfile($profile);
                    $user->setPersonType(UserProxyIntertace::PERSON_CUSTOMER);
                    $user->setCreatedAt(new \DateTimeImmutable());
                    $user->setCode($code);

                    $this->em->persist($user);
                } catch (\Exception $e) {
                    // Log l'erreur et transformer en UnavailableDataException
                    throw new UnavailableDataException('Error creating new user: ' . $e->getMessage());
                }
            } elseif ($user->isDeleted()) {
                // Si l'utilisateur est marqué comme supprimé, on déclenche une exception
                $this->em->flush(); // Sauvegarde la session comme validée
                throw new \App\Exception\UserAuthenticationException('This user is not active. Please contact support.');
            }

            $this->em->flush();
            return $user;
        } catch (UnavailableDataException $e) {
            throw $e;
        } catch (\App\Exception\UserAuthenticationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new UnavailableDataException('Error during OTP verification: ' . $e->getMessage());
        }
    }
}
