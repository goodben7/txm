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
use App\Message\Command\CommandBusInterface;
use App\Message\Command\CreateAuthUserCommand;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $em,
        private AuthSessionRepository $authSessionRepo,
        private UserRepository $userRepository,
        private ProfileRepository $profileRepository,
        private EventDispatcherInterface $eventDispatcher,
        private CodeGeneratorService $codeGeneratorService,
        private CommandBusInterface $bus
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
                    $command = new CreateAuthUserCommand(
                        $phone,
                        null, // Le code sera généré par le handler si null
                        UserProxyIntertace::PERSON_CUSTOMER
                    );
                    
                    // Dispatch la commande et récupérer l'utilisateur créé
                    $user = $this->bus->dispatch($command);
                        
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
