<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\UserAuthenticationException;
use App\Repository\CustomerRepository;

class CustomerActivationChecker
{
    public function __construct(
        private CustomerRepository $customerRepository
    ) {
    }

    /**
     * Vérifie si un client est activé et lance une exception si ce n'est pas le cas
     * 
     * @param string $customerId L'identifiant du client à vérifier
     * @throws UserAuthenticationException Si le client n'est pas activé
     */
    public function checkCustomerIsActivated(string $customerId): void
    {
        $customer = $this->customerRepository->find($customerId);
        
        if (!$customer) {
            throw new UserAuthenticationException('Customer not found.');
        }
        
        if (!$customer->getIsActivated()) {
            throw new UserAuthenticationException('This customer account is not activated. Please contact support.');
        }
    }

    /**
     * Vérifie si un utilisateur est associé à un client activé
     * 
     * @param User $user L'utilisateur à vérifier
     * @return bool True si l'utilisateur est associé à un client activé, false sinon
     */
    public function isUserCustomerActivated(User $user): bool
    {
        // Vérifier si l'utilisateur est de type client (PERSON_SENDER)
        if ($user->getPersonType() !== 'SENDER') {
            return true; // Si ce n'est pas un client, on considère qu'il est activé
        }
        
        // Récupérer l'ID du client associé à l'utilisateur
        $holderId = $user->getHolderId();
        if (!$holderId) {
            return false;
        }
        
        // Récupérer le client et vérifier son état d'activation
        $customer = $this->customerRepository->find($holderId);
        if (!$customer) {
            return false;
        }
        
        return $customer->getIsActivated();
    }
}