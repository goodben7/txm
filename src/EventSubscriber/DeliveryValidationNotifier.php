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

class DeliveryValidationNotifier implements EventSubscriberInterface {

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
            ActivityEvent::getEventName(Delivery::class, Delivery::EVENT_DELIVERY_VALIDATED) => 'onDeliveryValidation',
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

    public function onDeliveryValidation(ActivityEvent $event)
    {
        /** @var Delivery */
        $delivery = $event->getRessource();

        if (null === $delivery) {
            return;
        }

        try {
            // Créer une notification email
            $emailNotification = new Notification();
            $emailNotification->setType(NotificationType::DELIVERY_ASSIGNED);
            $emailNotification->setSubject('Livraison validée et livreur assigné');
            $emailNotification->setTitle('Livraison validée');
            
            // Préparer les informations d'adresse
            $pickupAddressText = $delivery->getPickupAddress() ? $delivery->getPickupAddress()->getAddress() : 'Non spécifiée';
            $deliveryAddressText = $delivery->getDeliveryAddress() ? $delivery->getDeliveryAddress()->getAddress() : 'Non spécifiée';
            
            // Obtenir le statut en format lisible
            $statusText = $this->getStatusText($delivery->getStatus());
            
            // Obtenir les informations du livreur
            $deliveryPersonText = $delivery->getDeliveryPerson() ? $delivery->getDeliveryPerson()->getFullname() : 'Non assigné';
            $deliveryPersonPhoneText = $delivery->getDeliveryPerson() && $delivery->getDeliveryPerson()->getPhone() ? $delivery->getDeliveryPerson()->getPhone() : 'Non disponible';
            
            // Mettre un message simple dans le corps
            $emailNotification->setBody("Votre livraison a été validée et un livreur a été assigné. Veuillez consulter les détails ci-dessous.");
            $emailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
            
            // Placer toutes les informations détaillées dans data
            $emailNotification->setData([
                'Numéro de suivi' => $delivery->getTrackingNumber(),
                'Date prévue' => $delivery->getDeliveryDate()->format('d/m/Y'),
                'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                'Statut' => $statusText,
                'Description' => $delivery->getDescription() ?: 'Aucune description',
                'Destinataire' => $delivery->getRecipient() ? $delivery->getRecipient()->getFullname() . ' (' . ($delivery->getRecipient()->getPhone() ?: 'Pas de téléphone') . ')' : 'Non spécifié',
                'Adresse de ramassage' => $pickupAddressText,
                'Adresse de livraison' => $deliveryAddressText,
                'Informations supplémentaires' => $delivery->getAdditionalInformation() ?: 'Aucune'
            ]);
            
            // Envoyer un email au client si disponible
            if ($delivery->getCustomer() && $delivery->getCustomer()->getEmail()) {
                $customerEmailNotification = clone $emailNotification;
                $customerEmailNotification->setTarget($delivery->getCustomer()->getEmail());
                $customerEmailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
                $this->entityManager->persist($customerEmailNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($customerEmailNotification));
            }

            // Envoyer un message WhatsApp au client si disponible
            if ($delivery->getCustomer() && $delivery->getCustomer()->getPhone()) {
                $customerWhatsappNotification = new Notification();
                $customerWhatsappNotification->setType(NotificationType::DELIVERY_ASSIGNED);
                $customerWhatsappNotification->setSubject('Livraison validée');
                $customerWhatsappNotification->setTitle('Livraison validée');
                $customerWhatsappNotification->setBody('Votre livraison a été validée et un livreur a été assigné.');
                $customerWhatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
                $customerWhatsappNotification->setTarget($delivery->getCustomer()->getPhone());
                $customerWhatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                $customerWhatsappNotification->setData([
                    'Numéro de suivi' => $delivery->getTrackingNumber(),
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
            
            // Créer une notification WhatsApp pour le destinataire
            $recipientWhatsappNotification = new Notification();
            $recipientWhatsappNotification->setType(NotificationType::DELIVERY_ASSIGNED);
            $recipientWhatsappNotification->setSubject('Nouvelle livraison');
            $recipientWhatsappNotification->setTitle('Nouvelle livraison');
            $recipientWhatsappNotification->setBody('Bonjour, Une nouvelle livraison a été créée et un livreur a été assigné. Veuillez consulter les détails ci-dessous.');
            $recipientWhatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
            $recipientWhatsappNotification->setData([
                'Numéro de suivi' => $delivery->getTrackingNumber(),
                'Date prévue' => $delivery->getDeliveryDate()->format('d/m/Y'),
                'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                'Statut' => $statusText,
                'Description' => $delivery->getDescription() ?: 'Aucune description',
                'Marchand' => $delivery->getCustomer() ? $delivery->getCustomer()->getCompanyName() . ' - ' . $delivery->getCustomer()->getFullname() . ' (' . ($delivery->getCustomer()->getPhone() ?: 'Pas de téléphone') . ')' : 'Non spécifié',
                'Adresse de livraison' => $deliveryAddressText,
                'Informations supplémentaires' => $delivery->getAdditionalInformation() ?: 'Aucune'
            ]);
            
            // Envoyer au numéro de téléphone du destinataire si disponible
            if ($delivery->getRecipient() && $delivery->getRecipient()->getPhone()) {
                $recipientWhatsappNotification->setTarget($delivery->getRecipient()->getPhone());
                $recipientWhatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                $this->entityManager->persist($recipientWhatsappNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($recipientWhatsappNotification));
            }
            
            // Envoyer un email au destinataire si disponible
            if ($delivery->getRecipient() && $delivery->getRecipient()->getEmail()) {
                $recipientEmailNotification = new Notification();
                $recipientEmailNotification->setType(NotificationType::DELIVERY_ASSIGNED);
                $recipientEmailNotification->setSubject('Nouvelle livraison');
                $recipientEmailNotification->setTitle('Nouvelle livraison');
                $recipientEmailNotification->setBody("Bonjour, Une nouvelle livraison a été créée et un livreur a été assigné. Veuillez consulter les détails ci-dessous.");

                $recipientEmailNotification->setData([
                    'Numéro de suivi' => $delivery->getTrackingNumber(),
                    'Date prévue' => $delivery->getDeliveryDate()->format('d/m/Y'),
                    'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                    'Statut' => $statusText,
                    'Description' => $delivery->getDescription() ?: 'Aucune description',
                    'Marchand' => $delivery->getCustomer() ? $delivery->getCustomer()->getCompanyName() . ' - ' . $delivery->getCustomer()->getFullname() . ' (' . ($delivery->getCustomer()->getPhone() ?: 'Pas de téléphone') . ')' : 'Non spécifié',
                    'Adresse de livraison' => $deliveryAddressText,
                    'Informations supplémentaires' => $delivery->getAdditionalInformation() ?: 'Aucune'
                ]);
                
                $recipientEmailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
                $recipientEmailNotification->setTarget($delivery->getRecipient()->getEmail());
                $recipientEmailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
                $this->entityManager->persist($recipientEmailNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($recipientEmailNotification));
            }
            
            // Envoyer un message WhatsApp au livreur si disponible
            if ($delivery->getDeliveryPerson() && $delivery->getDeliveryPerson()->getPhone()) {
                $deliveryPersonWhatsappNotification = new Notification();
                $deliveryPersonWhatsappNotification->setType(NotificationType::DELIVERY_ASSIGNED);
                $deliveryPersonWhatsappNotification->setSubject('Nouvelle livraison assignée');
                $deliveryPersonWhatsappNotification->setTitle('Nouvelle livraison');
                $deliveryPersonWhatsappNotification->setBody('Bonjour, une nouvelle livraison vous a été assignée.');
                $deliveryPersonWhatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
                $deliveryPersonWhatsappNotification->setTarget($delivery->getDeliveryPerson()->getPhone());
                $deliveryPersonWhatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                
                // Données résumées pour WhatsApp
                $deliveryPersonWhatsappNotification->setData([
                    'Numéro de suivi' => $delivery->getTrackingNumber(),
                    'Date prévue' => $delivery->getDeliveryDate()->format('d/m/Y'),
                    'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                    'Statut' => $statusText,
                    'Description' => $delivery->getDescription() ?: 'Aucune description',
                    'Adresse de ramassage' => $pickupAddressText,
                    'Adresse de livraison' => $deliveryAddressText,
                    'Marchand' => $delivery->getCustomer() ? $delivery->getCustomer()->getCompanyName() . ' - ' . $delivery->getCustomer()->getFullname() . ' (' . ($delivery->getCustomer()->getPhone() ?: 'Pas de téléphone') . ')' : 'Non spécifié',
                    'Destinataire' => $delivery->getRecipient() ? $delivery->getRecipient()->getFullname() . ' (' . ($delivery->getRecipient()->getPhone() ?: 'Pas de téléphone') . ')' : 'Non spécifié',
                    'Informations supplémentaires' => $delivery->getAdditionalInformation() ?: 'Aucune'
                ]);
                
                $this->entityManager->persist($deliveryPersonWhatsappNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($deliveryPersonWhatsappNotification));
            }
            
            // Enregistrer toutes les notifications en base de données
            $this->entityManager->flush();
        }
        catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
        }
    }  
}