<?php

namespace App\EventSubscriber;

use App\Event\OtpSentEvent;
use App\Entity\Notification;
use App\Enum\NotificationType;
use App\Message\SendNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OtpSentNotifier implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
        private MessageBusInterface $bus
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OtpSentEvent::NAME => 'onOtpSent',
        ];
    }

    public function onOtpSent(OtpSentEvent $event): void
    {
        $authSession = $event->getAuthSession();
        $phone = $authSession->getPhone();
        $otpCode = $authSession->getOtpCode();

        // Log the event
        $this->logger->info('OTP sent to phone number: {phone}', [
            'phone' => $phone,
        ]);

        $notification = new Notification();
        $notification->setType(NotificationType::TYPE_OTP);
        $notification->setSentVia(Notification::SENT_VIA_WHATSAPP);
        $notification->setTarget($phone);
        $notification->setTargetType(Notification::TARGET_TYPE_PHONE);
        $notification->setTitle('Code de vérification');
        $notification->setBody("Votre code de vérification est : {$otpCode}. Ce code est valide pour 5 minutes.");

        $this->em->persist($notification);
        $this->em->flush();

        $this->bus->dispatch(new SendNotificationMessage($notification));
    }
}