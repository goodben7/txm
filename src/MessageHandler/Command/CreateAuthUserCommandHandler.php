<?php

namespace App\MessageHandler\Command;

use App\Entity\Profile;
use App\Entity\User;
use App\Manager\UserManager;
use App\Model\UserProxyIntertace;
use Psr\Log\LoggerInterface;
use App\Repository\ProfileRepository;
use App\Service\CodeGeneratorService;
use App\Message\Command\CreateAuthUserCommand;
use App\Message\Command\CommandHandlerInterface;
use App\Exception\UnavailableDataException;

class CreateAuthUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private UserManager $manager,
        private ProfileRepository $profileRepository,
        private CodeGeneratorService $codeGeneratorService
    ) { 
    }

    /**
     * Summary of __invoke
     * @param \App\Message\Command\CreateAuthUserCommand $command
     * @throws \Exception
     * @return User
     */
    public function __invoke(CreateAuthUserCommand $command): User
    {
        try {
            // Récupérer le profil CUSTOMER
            $personType = $command->personType ?? UserProxyIntertace::PERSON_CUSTOMER;
            /**
             * @var Profile $profile
             */
            $profile = $this->profileRepository->findOneBy(['personType' => $personType]);

            if (null === $profile) {
                throw new UnavailableDataException('cannot find profile with person type: ' . $personType);
            }

            // Générer un code unique
            $entityName = 'Recipient';
            $code = $command->code ?? $this->codeGeneratorService->generateCode($entityName, $personType);
                
            if ($this->codeGeneratorService->codeExists($code)) {
                throw new UnavailableDataException('code already exists');
            }

            // Créer l'utilisateur
            $user = new User();
            $user->setPhone($command->phone);
            $user->setPassword(null); // Mot de passe null pour les utilisateurs authentifiés par OTP
            $user->setPlainPassword(null); // Initialiser explicitement plainPassword pour éviter l'erreur
            $user->setDeleted(false);
            $user->setProfile($profile);
            $user->setPersonType($personType);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setCode($code);
            
            return $this->manager->create($user);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new \Exception('Error in CreateAuthUserCommandHandler: ' . $e->getMessage(), 0, $e);
        }
    }
}