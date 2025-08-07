<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\Document;
use App\Repository\UserRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Exception\InvalidActionInputException;

class DocumentManager
{
    public function __construct(
        private Security $security, 
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
    ) {   
    }

    public function reject(Document $document, string $rejectionReason) : Document
    {

        if ($document->getStatus() !== Document::STATUS_PENDING) {
            throw new InvalidActionInputException('Action not allowed : invalid document state');
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $document->setStatus(Document::STATUS_REFUSED);
        $document->setRejectionReason($rejectionReason);
        $document->setRejectedAt(new \DateTimeImmutable('now'));
        $document->setRejectedBy($user->getId());

        $this->em->persist($document);
        $this->em->flush();

        $this->eventDispatcher->dispatch(
            $document, 
            Document::EVENT_DOCUMENT_REJECT, 
            null, 
            $rejectionReason)
        ;

        return $document; 

    }

    public function validate(Document $document) : Document
    {

        if ($document->getStatus() !== Document::STATUS_PENDING) {
            throw new InvalidActionInputException('Action not allowed : invalid document state');
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $document->setStatus(Document::STATUS_VALIDATED);
        $document->setValidatedAt(new \DateTimeImmutable('now'));
        $document->setValidatedBy($user->getId());

        $this->em->persist($document);
        $this->em->flush();

        $this->eventDispatcher->dispatch(
            $document, 
            Document::EVENT_DOCUMENT_VALIDATE, 
            null, 
            null)
        ;

        return $document; 
        
    }
}
