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
            ->subject($notification->getSubject());
        
        // Utiliser un template spécifique pour les notifications de création de compte
        if ($notification->getType() === \App\Enum\NotificationType::NEW_ACCOUNT_CREATED) {
            $email->htmlTemplate('email/new_user_details.html.twig')
                ->context([
                    'user' => [
                        'email' => $notification->getData()['Identifiant'] ?? null,
                        'displayName' => $notification->getData()['Nom'] ?? null,
                        'phone' => $notification->getData()['Téléphone'] ?? null,
                        'createdAt' => new \DateTimeImmutable($notification->getData()['Date d\'inscription'] ?? 'now')
                    ]
                ]);
        } else {
            $email->htmlTemplate('email/notification_generic.html.twig')
                ->context(['notification' => $notification]);
        }
            
        $this->mailer->send($email);
    }

    public function support(string $sentVia): bool
    {
        return $sentVia === Notification::SENT_VIA_GMAIL;
    }
}