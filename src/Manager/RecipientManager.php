<?php

namespace App\Manager;

use App\Entity\Recipient;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use App\Exception\UnauthorizedActionException;

class RecipientManager
{
    public function __construct(
        private EntityManagerInterface $em, 
    )
    {
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