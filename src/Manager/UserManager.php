<?php

namespace App\Manager;

use App\Entity\User;
use App\Model\NewUserModel;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use App\Exception\InvalidActionInputException;
use App\Exception\UnauthorizedActionException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $em, 
        private UserPasswordHasherInterface $hasher
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
        $user->setRoles([$model->roles]);


        $this->em->persist($user);
        $this->em->flush();
        
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

}