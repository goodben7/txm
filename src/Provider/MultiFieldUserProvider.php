<?php

namespace App\Provider;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class MultiFieldUserProvider implements UserProviderInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Loads the user for the given user identifier (username).
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Tente de trouver par email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $identifier]);

        if ($user) {
            return $user;
        }

        // Tente de trouver par numéro de téléphone
        // Assurez-vous que votre numéro de téléphone est stocké dans un format unique et sans espace/caractères spéciaux.
        // Vous devrez peut-être normaliser le numéro ici avant la recherche.
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['phone' => $identifier]);

        if ($user) {
            return $user;
        }

        // Si l'utilisateur n'est pas trouvé ni par email ni par téléphone
        throw new UserNotFoundException(sprintf('User with "%s" not found.', $identifier));
    }

    /**
     * @deprecated since Symfony 5.3, use loadUserByIdentifier() instead
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        // Recharger l'utilisateur pour s'assurer que ses données sont à jour
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}