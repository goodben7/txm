<?php

namespace App\EventSubscriber;

use App\Entity\Notification;
use App\Entity\Store;
use App\Event\ActivityEvent;
use App\Enum\NotificationType;
use App\Message\SendNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StoreVerificationNotifier implements EventSubscriberInterface 
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
            ActivityEvent::getEventName(Store::class, Store::EVENT_STORE_VERIFIED) => 'onStoreVerification',
        ];
    }

    public function onStoreVerification(ActivityEvent $event)
    {
        /** @var Store */
        $store = $event->getRessource();

        if (null === $store) {
            return;
        }

        $customer = $store->getCustomer();

        if (null === $customer) {
            // Maybe log this case
            return;
        }

        try {
            // Créer une notification email si le client a un email
            if ($customer->getEmail()) {
                $emailNotification = new Notification();
                $emailNotification->setType(NotificationType::ACCOUNT_ACTIVATED);
                $emailNotification->setSubject('Validation de votre Boutique');
                $emailNotification->setTitle('Votre Boutique a été validée');
                $emailNotification->setBody(sprintf('Félicitations ! Votre Boutique "%s" a été validée avec succès. Elle est maintenant visible par les utilisateurs.', $store->getLabel()));
                $emailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
                $emailNotification->setTarget($customer->getEmail());
                $emailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
                
                // Ajouter les données client pour le template
                $emailNotification->setData([
                    'Boutique' => $store->getLabel(),
                    'Client' => $customer->getFullname() ?? 'Client',
                    'Date de validation' => (new \DateTimeImmutable('now'))->format('d/m/Y'),
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
                $whatsappNotification->setSubject('Validation de Boutique');
                $whatsappNotification->setTitle('Boutique validée');
                $whatsappNotification->setBody(sprintf('Félicitations ! Votre Boutique "%s" a été validée avec succès sur la plateforme TINDA.', $store->getLabel()));
                $whatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
                $whatsappNotification->setTarget($customer->getPhone());
                $whatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                
                // Ajouter les données client pour le template WhatsApp
                $whatsappNotification->setData([
                    'Boutique' => $store->getLabel(),
                    'Client' => $customer->getFullname() ?? 'Client',
                    'Date de validation' => (new \DateTimeImmutable('now'))->format('d/m/Y'),
                ]);
                
                // Persister et envoyer la notification
                $this->entityManager->persist($whatsappNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($whatsappNotification));
            }
            
            // Flush pour sauvegarder les notifications
            $this->entityManager->flush();
        }
        catch (\Exception $e) {
            $this->logger->warning('Erreur lors de l\'envoi des notifications de validation de Boutique: ' . $e->getMessage(), ['exception' => $e]);
        }
    }
}