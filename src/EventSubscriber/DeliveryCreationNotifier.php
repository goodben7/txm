<?php

namespace App\EventSubscriber;

use App\Entity\Delivery;
use App\Entity\Notification;
use App\Event\ActivityEvent;
use App\Enum\NotificationType;
use App\Message\SendNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeliveryCreationNotifier implements EventSubscriberInterface {

    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private string $adminEmail,
        private string $adminPhone
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActivityEvent::getEventName(Delivery::class, Delivery::EVENT_DELIVERY_CREATED) => 'onDeliveryCreation',
        ];
    }

    /**
     * Convertit le code de statut en texte lisible
     */
    private function getStatusText(string $status): string
    {
        return match($status) {
            Delivery::STATUS_PENDING => 'En attente',
            Delivery::STATUS_VALIDATED => 'Validée',
            Delivery::STATUS_PICKUPED => 'Ramassée',
            Delivery::STATUS_INPROGRESS => 'En cours',
            Delivery::STATUS_DELAYED => 'Retardée',
            Delivery::STATUS_TERMINATED => 'Terminée',
            Delivery::STATUS_CANCELED => 'Annulée',
            default => 'Statut inconnu'
        };
    }

    public function onDeliveryCreation(ActivityEvent $event)
    {
        /** @var Delivery */
        $delivery = $event->getRessource();

        if (null === $delivery) {
            return;
        }

        try {
            // Préparer les informations d'adresse
            $pickupAddressText = $delivery->getPickupAddress() ? $delivery->getPickupAddress()->getAddress() : 'Non spécifiée';
            $deliveryAddressText = $delivery->getDeliveryAddress() ? $delivery->getDeliveryAddress()->getAddress() : 'Non spécifiée';
            
            // Obtenir le statut en format lisible
            $statusText = $this->getStatusText($delivery->getStatus());
            
            // Envoyer un email au client si disponible
            if ($delivery->getCustomer() && $delivery->getCustomer()->getEmail()) {
                $customerEmailNotification = new Notification();
                $customerEmailNotification->setType(NotificationType::DELIVERY_CREATED);
                $customerEmailNotification->setSubject('Nouvelle livraison créée');
                $customerEmailNotification->setTitle('Nouvelle livraison créée');
                $customerEmailNotification->setBody("Votre livraison a été créée avec succès. Veuillez consulter les détails ci-dessous.");
                $customerEmailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
                $customerEmailNotification->setTarget($delivery->getCustomer()->getEmail());
                $customerEmailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
                
                // Placer toutes les informations détaillées dans data
                $customerEmailNotification->setData([
                    'Numéro de suivi' => $delivery->getTrackingNumber(),
                    'Creé le' => $delivery->getCreatedAt()->format('d/m/Y à H:i:s'),
                    'Date prévue' => $delivery->getDeliveryDate()->format('d/m/Y'),
                    'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                    'Statut' => $statusText,
                    'Description' => $delivery->getDescription() ?: 'Aucune description',
                    'Destinataire' => $delivery->getRecipient() ? $delivery->getRecipient()->getFullname() . ' (' . ($delivery->getRecipient()->getPhone() ?: 'Pas de téléphone') . ')' : 'Non spécifié',
                    'Adresse de ramassage' => $pickupAddressText,
                    'Adresse de livraison' => $deliveryAddressText,
                    'Informations supplémentaires' => $delivery->getAdditionalInformation() ?: 'Aucune'
                ]);
                
                $this->entityManager->persist($customerEmailNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($customerEmailNotification));
            }

            // Envoyer un message WhatsApp au client si disponible
            if ($delivery->getCustomer() && $delivery->getCustomer()->getPhone()) {
                $customerWhatsappNotification = new Notification();
                $customerWhatsappNotification->setType(NotificationType::DELIVERY_CREATED);
                $customerWhatsappNotification->setSubject('Nouvelle livraison créée');
                $customerWhatsappNotification->setTitle('Nouvelle livraison créée');
                $customerWhatsappNotification->setBody('Votre livraison a été créée avec succès.');
                $customerWhatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
                $customerWhatsappNotification->setTarget($delivery->getCustomer()->getPhone());
                $customerWhatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                $customerWhatsappNotification->setData([
                    'Numéro de suivi' => $delivery->getTrackingNumber(),
                    'Creé le' => $delivery->getCreatedAt()->format('d/m/Y à H:i:s'),
                    'Date prévue' => $delivery->getDeliveryDate()->format('d/m/Y'),
                    'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                    'Statut' => $statusText,
                    'Description' => $delivery->getDescription() ?: 'Aucune description',
                    'Destinataire' => $delivery->getRecipient() ? $delivery->getRecipient()->getFullname() . ' (' . ($delivery->getRecipient()->getPhone() ?: 'Pas de téléphone') . ')' : 'Non spécifié',
                    'Adresse de ramassage' => $pickupAddressText,
                    'Adresse de livraison' => $deliveryAddressText,
                    'Informations supplémentaires' => $delivery->getAdditionalInformation() ?: 'Aucune'
                ]);
                $this->entityManager->persist($customerWhatsappNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($customerWhatsappNotification));
            }
            
            // Enregistrer toutes les notifications en base de données
            $this->entityManager->flush();
        }
        catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
        }
    }  
}