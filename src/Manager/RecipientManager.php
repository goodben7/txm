<?php

namespace App\Manager;

use App\Entity\Recipient;
use App\Model\NewRecipientModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use App\Exception\UnauthorizedActionException;

class RecipientManager
{
    public function __construct(
        private EntityManagerInterface $em, 
        private ActivityEventDispatcher $eventDispatcher,
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

        $this->em->persist($r);
        $this->em->flush();

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