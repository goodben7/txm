<?php

namespace App\Service;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use App\Contract\NotificationSenderInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class NotificationService
{
    /** @var NotificationSenderInterface[] */
    private iterable $senders;

    public function __construct(
        private readonly EntityManagerInterface $em,
        #[TaggedIterator('app.notification_sender')] iterable $senders
    )
    {
        $this->senders = iterator_to_array($senders);
    }

    public function send(Notification $notification): void
    {
        foreach ($this->senders as $sender) {
            if ($sender->support($notification->getSentVia())) {
                $sender->send($notification);

                $notification->setReadAt(new \DateTimeImmutable());

                $this->em->flush();

                return;
            }
        }

        throw new \RuntimeException('No sender found for notification type: ' . $notification->getSentVia());
    }
}
