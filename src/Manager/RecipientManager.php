<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\Recipient;
use App\Model\NewRecipientModel;
use App\Model\UserProxyIntertace;
use App\Repository\UserRepository;
use App\Repository\ProfileRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use App\Exception\UnauthorizedActionException;

class RecipientManager
{
    public function __construct(
        private EntityManagerInterface $em, 
        private ActivityEventDispatcher $eventDispatcher,
        private ProfileRepository $profileRepository,
        private UserRepository $userRepository,
    )
    {
    }

    public function createFrom(NewRecipientModel $model): Recipient {

        $r = new Recipient();
    
        $r->setCustomer($model->customer);
        $r->setFullname($model->fullname);
        $r->setPhone($model->phone);
        $r->setPhone2($model->phone2);
        $r->setEmail($model->email);
        $r->setCreatedAt(new \DateTimeImmutable('now'));
        $r->setRecipientType($model->recipientType);

        foreach ($model->addresses as $addr) {
            $r->addAddress($addr);
        }

        try {
            $this->em->persist($r);
            $this->em->flush();
        } catch (\Exception $e) {
            throw new UnavailableDataException($e->getMessage());
        }

        $profile = $this->profileRepository->findOneBy(['personType' => UserProxyIntertace::PERSON_CUSTOMER]);

        if (null === $profile) {
            throw new UnavailableDataException('cannot find profile with person type: customer');
        }

        $user = $this->userRepository->findOneBy(['phone' => $model->phone]);

        if (null === $user) {
            $user = new User();
            $user->setPhone($model->phone);
            $user->setPassword(null); // Définir le mot de passe à null pour les utilisateurs authentifiés par OTP
            $user->setDeleted(false);
            $user->setEmail($model->email);
            $user->setProfile($profile);
            $user->setDisplayName($model->fullname);
            $user->setPersonType(UserProxyIntertace::PERSON_CUSTOMER);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setHolderId($r->getId());
        } else {
            $user->setEmail($model->email);
            $user->setDisplayName($model->fullname);
            $user->setHolderId($r->getId());
        }

        try {
            $this->em->persist($user);
            $this->em->flush();

            $r->setUserId($user->getId());

            $this->em->persist($r);
            $this->em->flush();
        } catch (\Exception $e) {
            throw new UnavailableDataException($e->getMessage());
        }

        $this->eventDispatcher->dispatch($r, Recipient::EVENT_USER_RECIPIENT);
        
        return $r;
    }


    private function findRecipient(string $recipientId): Recipient 
    {
        $recipient = $this->em->find(Recipient::class, $recipientId);

        if (null === $recipient) {
            throw new UnavailableDataException(sprintf('cannot find recipientId with id: %s', $recipientId));
        }

        return $recipient; 
    }

    public function delete(string $recipientId) {
        $recipient = $this->findRecipient($recipientId);

        if ($recipient->isDeleted()) {
            throw new UnauthorizedActionException('this action is not allowed');
        }

        $recipient->setDeleted(true);
        $recipient->setUpdatedAt(new \DateTimeImmutable('now'));

        $this->em->persist($recipient);
        $this->em->flush();
    }
}