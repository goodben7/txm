<?php

namespace App\Service;

use App\Entity\Notification;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Contract\NotificationSenderInterface;
use Symfony\Component\Mailer\MailerInterface;

readonly class EmailNotificationSender implements NotificationSenderInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private string $mailerSender,
        private string $mailerSenderName
    ) {
    }

    public function send(Notification $notification): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerSender, $this->mailerSenderName))
            ->to(new Address(($notification->getTarget())))
            ->subject($notification->getSubject())
            ->htmlTemplate('email/notification_generic.html.twig')
            ->context(['notification' => $notification])
        ;
            
        $this->mailer->send($email);
    }

    public function support(string $sentVia): bool
    {
        return $sentVia === Notification::SENT_VIA_GMAIL;
    }
}