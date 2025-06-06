<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Event\ActivityEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserCreationEmailNotifier implements EventSubscriberInterface {

    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger
    )
    {
        
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            ActivityEvent::getEventName(User::class, User::EVENT_USER_CREATED) => 'onRegistration',
        ];
    }

    public function onRegistration(ActivityEvent $event)
    {
        /** @var User  */
        $user = $event->getRessource();

        if (null !== $user && $user->getEmail()) {
            try {
                $email = (new TemplatedEmail())
                    ->from(new Address('mailler@pteron.pro', 'TINDA'))
                    ->to(new Address($user->getEmail()))
                    ->subject('Nouvel inscription')
                    ->htmlTemplate('email/new_user_details.html.twig')
                    ->context([
                        'user' => $user,
                    ])
                ;
            
                $this->mailer->send($email);
            }
            catch (\Exception $e) {
                $this->logger->warning($e->getMessage(), ['exception' => $e]);
            }
        }
    }
}