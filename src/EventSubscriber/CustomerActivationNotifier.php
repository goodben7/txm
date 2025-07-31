<?php

namespace App\EventSubscriber;

use App\Entity\Customer;
use App\Entity\Notification;
use App\Event\ActivityEvent;
use App\Enum\NotificationType;
use App\Message\SendNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CustomerActivationNotifier implements EventSubscriberInterface 
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
            ActivityEvent::getEventName(Customer::class, Customer::EVENT_CUSTOMER_ACTIVATED) => 'onCustomerActivation',
        ];
    }

    public function onCustomerActivation(ActivityEvent $event)
    {
        /** @var Customer */
        $customer = $event->getRessource();

        if (null === $customer) {
            return;
        }

        try {
            // Créer une notification email si le client a un email
            if ($customer->getEmail()) {
                $emailNotification = new Notification();
                $emailNotification->setType(NotificationType::ACCOUNT_ACTIVATED);
                $emailNotification->setSubject('Activation de compte');
                $emailNotification->setTitle('Votre compte a été activé');
                $emailNotification->setBody('Félicitations ! Votre compte a été activé avec succès. Vous pouvez maintenant utiliser tous les services de la plateforme TINDA.');
                $emailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
                $emailNotification->setTarget($customer->getEmail());
                $emailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
                
                // Ajouter les données client pour le template
                $emailNotification->setData([
                    'Entreprise' => $customer->getCompanyName() ?? 'Non spécifié',
                    'Nom' => $customer->getFullname() ?? 'Client',
                    'Date d\'activation' => (new \DateTimeImmutable('now'))->format('d/m/Y'),
                    'Téléphone' => $customer->getPhone() ?? 'Non spécifié',
                    'Email' => $customer->getEmail() ?? 'Non spécifié'
                ]);
                
                // Persister et envoyer la notification
                $this->entityManager->persist($emailNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($emailNotification));
            }
            
            // Créer une notification WhatsApp si le client a un numéro de téléphone
            if ($customer->getPhone()) {
                $whatsappNotification = new Notification();
                $whatsappNotification->setType(NotificationType::ACCOUNT_ACTIVATED);
                $whatsappNotification->setSubject('Activation de compte');
                $whatsappNotification->setTitle('Compte activé');
                $whatsappNotification->setBody('Félicitations ! Votre compte TINDA a été activé avec succès. Vous pouvez maintenant accéder à tous les services de notre plateforme de gestion de colis.');
                $whatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
                $whatsappNotification->setTarget($customer->getPhone());
                $whatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                
                // Ajouter les données client pour le template WhatsApp
                $whatsappNotification->setData([
                    'Entreprise' => $customer->getCompanyName() ?? 'Non spécifié',
                    'Nom' => $customer->getFullname() ?? 'Client',
                    'Date d\'activation' => (new \DateTimeImmutable('now'))->format('d/m/Y'),
                    'Email' => $customer->getEmail() ?? 'Non spécifié'
                ]);
                
                // Persister et envoyer la notification
                $this->entityManager->persist($whatsappNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($whatsappNotification));
            }
            
            // Flush pour sauvegarder les notifications
            $this->entityManager->flush();
        }
        catch (\Exception $e) {
            $this->logger->warning('Erreur lors de l\'envoi des notifications d\'activation de compte: ' . $e->getMessage(), ['exception' => $e]);
        }
    }
}