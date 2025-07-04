<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\Notification;
use App\Event\ActivityEvent;
use App\Enum\NotificationType;
use App\Message\SendNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserCreationEmailNotifier implements EventSubscriberInterface 
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager
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

        if (null === $user) {
            return;
        }

        try {
            // Créer une notification email si l'utilisateur a un email
            if ($user->getEmail()) {
                $emailNotification = new Notification();
                $emailNotification->setType(NotificationType::NEW_ACCOUNT_CREATED);
                $emailNotification->setSubject('Nouvel inscription');
                $emailNotification->setTitle('Bienvenue sur TINDA');
                $emailNotification->setBody('Votre compte a été créé avec succès. Veuillez consulter les détails ci-dessous.');
                $emailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
                $emailNotification->setTarget($user->getEmail());
                $emailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
                
                // Ajouter les données utilisateur pour le template
                $emailNotification->setData([
                    'Identifiant' => $user->getEmail(),
                    'Nom' => $user->getDisplayName() ?? 'Utilisateur',
                    'Date d\'inscription' => $user->getCreatedAt()->format('d/m/Y'),
                    'Téléphone' => $user->getPhone() ?? 'Non spécifié'
                ]);
                
                // Persister et envoyer la notification
                $this->entityManager->persist($emailNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($emailNotification));
            }
            
            // Créer une notification WhatsApp si l'utilisateur a un numéro de téléphone
            if ($user->getPhone()) {
                $whatsappNotification = new Notification();
                $whatsappNotification->setType(NotificationType::NEW_ACCOUNT_CREATED);
                $whatsappNotification->setSubject('Nouvel inscription');
                $whatsappNotification->setTitle('Bienvenue sur TINDA');
                $whatsappNotification->setBody('Votre compte a été créé avec succès. Bienvenue sur TINDA, votre plateforme de gestion de colis. Sécurité : Pour protéger votre compte, veuillez changer votre mot de passe temporaire dès votre première connexion.');
                $whatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
                $whatsappNotification->setTarget($user->getPhone());
                $whatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                
                // Ajouter les données utilisateur pour le template WhatsApp
                $whatsappNotification->setData([
                    'Identifiant' => $user->getEmail() ?? 'Non spécifié',
                    'Nom' => $user->getDisplayName() ?? 'Utilisateur',
                    'Date d\'inscription' => $user->getCreatedAt()->format('d/m/Y'),
                    'Mot de passe temporaire' => $user->getEmail() ?: ($user->getPhone() ?: 'Contactez l\'administrateur')
                ]);
                
                // Persister et envoyer la notification
                $this->entityManager->persist($whatsappNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($whatsappNotification));
            }
            
            // Flush pour sauvegarder les notifications
            $this->entityManager->flush();
        }
        catch (\Exception $e) {
            $this->logger->warning('Erreur lors de l\'envoi des notifications d\'inscription: ' . $e->getMessage(), ['exception' => $e]);
        }
    }
}