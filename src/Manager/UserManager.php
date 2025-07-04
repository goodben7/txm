<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\Profile;
use App\Model\NewUserModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use App\Exception\InvalidActionInputException;
use App\Exception\UnauthorizedActionException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $em, 
        private UserPasswordHasherInterface $hasher,
        private ActivityEventDispatcher $eventDispatcher,
    )
    {
    }

    public function createFrom(NewUserModel $model): User {

        $user = new User();

        $user->setEmail($model->email);
        $user->setCreatedAt(new \DateTimeImmutable('now'));
        $user->setPassword($this->hasher->hashPassword($user, $model->plainPassword));
        $user->setPhone($model->phone);
        $user->setDisplayName($model->displayName);
        $user->setProfile($model->profile);
        $user->setPersonType($model->profile->getPersonType());
        $user->setHolderId($model->holderId);


        $this->em->persist($user);
        $this->em->flush();
        
        $this->eventDispatcher->dispatch($user, User::EVENT_USER_CREATED);

        return $user;
    }
    public function create(User $user): User 
    {

        if ($user->getPlainPassword()) {
            $user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));
            $user->eraseCredentials();
        }

        $user->setCreatedAt(new \DateTimeImmutable('now'));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function findUser(string $userId): User 
    {
        $user = $this->em->find(User::class, $userId);

        if (null === $user) {
            throw new UnavailableDataException(sprintf('cannot find user with id: %s', $userId));
        }

        return $user; 
    }

    public function delete(string $userId) {
        $user = $this->findUser($userId);

        if ($user->isDeleted()) {
            throw new UnauthorizedActionException('this action is not allowed');
        }

        $user->setDeleted(true);
        $user->setUpdatedAt(new \DateTimeImmutable('now'));

        $this->em->persist($user);
        $this->em->flush();
    }

    public function changePassword(string $userId, string $actualPassword, string $newPassword): User 
    {
        $user = $this->findUser($userId);


        if (!$this->hasher->isPasswordValid($user, $actualPassword)) {
            throw new InvalidActionInputException('the submitted actual password is not correct');
        }

        $user->setPassword($this->hasher->hashPassword($user, $newPassword));
        $user->setUpdatedAt(new \DateTimeImmutable('now'));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function lockOrUnlockUser(string|User $user): User
    {
        if (is_string($user)) {
            $user = $this->findUser($user);
        }

        $locked = $user->isLocked();
        $user->setLocked(!$locked);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function setUserProfile(string|User $user, null|string|Profile $profile = null): User 
    {
        if (is_string($user)) {
            $user = $this->findUser($user);
        }

        if (null === $profile) {
            $user->setProfile(null);
        }
        else {
            if (is_string($profile)) {
                /** @var Profile  */
                $profile = $this->em->find(Profile::class, $profile);
                if (null === $profile) {
                    throw new InvalidActionInputException(sprintf('cannot find profile with id: %s', $profile));
                }
            }
            

            if ($user->getPersonType() === null) {
                $user->setPersonType($profile->getPersonType());
            } elseif ($user->getPersonType() !== $profile->getPersonType()) {
                throw new InvalidActionInputException('invalid profile. Mismatch person type');
            }

            $user->setProfile($profile);
        }

        $this->em->persist($user);
        $this->em->flush();
        
        return $user;
    }
    

}