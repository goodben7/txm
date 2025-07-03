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

class DeliveryReassignmentNotifier implements EventSubscriberInterface {

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
            ActivityEvent::getEventName(Delivery::class, Delivery::EVENT_DELIVERY_REASSIGNED) => 'onDeliveryReassignment',
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

    public function onDeliveryReassignment(ActivityEvent $event)
    {
        /** @var Delivery */
        $delivery = $event->getRessource();

        if (null === $delivery) {
            return;
        }

        try {
            // Récupérer le message explicatif de la réassignation
            $reassignmentMessage = $delivery->getMessage() ?: 'Aucune raison spécifiée';
            
            // Créer une notification email
            $emailNotification = new Notification();
            $emailNotification->setType(NotificationType::DELIVERY_ASSIGNED);
            $emailNotification->setSubject('Livraison réassignée');
            $emailNotification->setTitle('Livraison réassignée');
            
            // Préparer les informations d'adresse
            $pickupAddressText = $delivery->getPickupAddress() ? $delivery->getPickupAddress()->getAddress() : 'Non spécifiée';
            $deliveryAddressText = $delivery->getDeliveryAddress() ? $delivery->getDeliveryAddress()->getAddress() : 'Non spécifiée';
            
            // Obtenir le statut en format lisible
            $statusText = $this->getStatusText($delivery->getStatus());
            
            // Obtenir les informations du livreur
            $deliveryPersonText = $delivery->getDeliveryPerson() ? $delivery->getDeliveryPerson()->getFullname() : 'Non assigné';
            $deliveryPersonPhoneText = $delivery->getDeliveryPerson() && $delivery->getDeliveryPerson()->getPhone() ? $delivery->getDeliveryPerson()->getPhone() : 'Non disponible';
            
            // Mettre un message simple dans le corps
            $emailNotification->setBody("Votre livraison a été réassignée à un nouveau livreur. Veuillez consulter les détails ci-dessous.");
            $emailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
            
            // Placer toutes les informations détaillées dans data
            $emailNotification->setData([
                'Numéro de suivi' => $delivery->getTrackingNumber(),
                'Date prévue' => $delivery->getDeliveryDate()->format('d/m/Y'),
                'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                'Statut' => $statusText,
                'Nouveau livreur assigné' => $deliveryPersonText,
                'Contact du livreur' => $deliveryPersonPhoneText,
                'Raison du changement' => $reassignmentMessage,
                'Description' => $delivery->getDescription() ?: 'Aucune description',
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
                $customerWhatsappNotification->setSubject('Livraison réassignée');
                $customerWhatsappNotification->setTitle('Livraison réassignée');
                $customerWhatsappNotification->setBody('Votre livraison a été réassignée à un nouveau livreur.');
                $customerWhatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
                $customerWhatsappNotification->setTarget($delivery->getCustomer()->getPhone());
                $customerWhatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                $customerWhatsappNotification->setData([
                    'Numéro' => $delivery->getTrackingNumber(),
                    'Date' => $delivery->getDeliveryDate()->format('d/m/Y'),
                    'Nouveau livreur' => $deliveryPersonText,
                    'Contact' => $deliveryPersonPhoneText,
                    'Raison' => substr($reassignmentMessage, 0, 100) . (strlen($reassignmentMessage) > 100 ? '...' : ''),
                    'Statut' => $statusText
                ]);
                $this->entityManager->persist($customerWhatsappNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($customerWhatsappNotification));
            }
            
            // Créer une notification WhatsApp pour le destinataire
            $recipientWhatsappNotification = new Notification();
            $recipientWhatsappNotification->setType(NotificationType::DELIVERY_ASSIGNED);
            $recipientWhatsappNotification->setSubject('Livraison réassignée');
            $recipientWhatsappNotification->setTitle('Livraison réassignée');
            $recipientWhatsappNotification->setBody('Bonjour, votre livraison a été réassignée à un nouveau livreur.');
            $recipientWhatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
            $recipientWhatsappNotification->setData([
                'Numéro' => $delivery->getTrackingNumber(),
                'Date' => $delivery->getDeliveryDate()->format('d/m/Y'),
                'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                'Nouveau livreur' => $deliveryPersonText,
                'Contact' => $deliveryPersonPhoneText,
                'Raison' => substr($reassignmentMessage, 0, 100) . (strlen($reassignmentMessage) > 100 ? '...' : ''),
                'Adresse de livraison' => $delivery->getDeliveryAddress() ? substr($delivery->getDeliveryAddress()->getAddress(), 0, 50) . '...' : 'Non spécifiée'
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
                $recipientEmailNotification->setSubject('Livraison réassignée - Nouveau livreur');
                $recipientEmailNotification->setTitle('Livraison réassignée');
                $recipientEmailNotification->setBody("Bonjour, votre livraison a été réassignée à un nouveau livreur. Veuillez consulter les détails ci-dessous.");

                $recipientEmailNotification->setData([
                    'Numéro de suivi' => $delivery->getTrackingNumber(),
                    'Date prévue' => $delivery->getDeliveryDate()->format('d/m/Y'),
                    'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                    'Statut' => $this->getStatusText($delivery->getStatus()),
                    'Nouveau livreur assigné' => $deliveryPersonText,
                    'Contact du livreur' => $deliveryPersonPhoneText,
                    'Raison du changement' => $reassignmentMessage,
                    'Description' => $delivery->getDescription() ?: 'Aucune description',
                    'Adresse de livraison' => $delivery->getDeliveryAddress() ? $delivery->getDeliveryAddress()->getAddress() : 'Non spécifiée',
                    'Informations supplémentaires' => $delivery->getAdditionalInformation() ?: 'Aucune'
                ]);
                
                $recipientEmailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
                $recipientEmailNotification->setTarget($delivery->getRecipient()->getEmail());
                $recipientEmailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
                $this->entityManager->persist($recipientEmailNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($recipientEmailNotification));
            }
            
            // Envoyer un email à l'administrateur
            $adminEmailNotification = new Notification();
            $adminEmailNotification->setType(NotificationType::DELIVERY_ASSIGNED);
            $adminEmailNotification->setSubject('[ADMIN] Livraison réassignée');
            $adminEmailNotification->setTitle('Livraison réassignée');
            $adminEmailNotification->setBody("Une livraison a été réassignée à un nouveau livreur. Veuillez consulter les détails ci-dessous.");
            $adminEmailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
            $adminEmailNotification->setTarget($this->adminEmail);
            $adminEmailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
            
            // Données complètes pour l'administrateur
            $adminEmailNotification->setData([
                'Numéro de suivi' => $delivery->getTrackingNumber(),
                'Date prévue' => $delivery->getDeliveryDate()->format('d/m/Y'),
                'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                'Statut' => $statusText,
                'Nouveau livreur assigné' => $deliveryPersonText,
                'Contact du livreur' => $deliveryPersonPhoneText,
                'Raison du changement' => $reassignmentMessage,
                'Description' => $delivery->getDescription() ?: 'Aucune description',
                'Adresse de ramassage' => $pickupAddressText,
                'Adresse de livraison' => $deliveryAddressText,
                'Client' => $delivery->getCustomer() ? $delivery->getCustomer()->getFullname() . ' (' . $delivery->getCustomer()->getEmail() . ')' : 'Non spécifié',
                'Destinataire' => $delivery->getRecipient() ? $delivery->getRecipient()->getFullname() . ' (' . $delivery->getRecipient()->getEmail() . ')' : 'Non spécifié',
                'Informations supplémentaires' => $delivery->getAdditionalInformation() ?: 'Aucune'
            ]);
            
            $this->entityManager->persist($adminEmailNotification);
            $this->messageBus->dispatch(new SendNotificationMessage($adminEmailNotification));
            
            // Envoyer un message WhatsApp à l'administrateur
            $adminWhatsappNotification = new Notification();
            $adminWhatsappNotification->setType(NotificationType::DELIVERY_ASSIGNED);
            $adminWhatsappNotification->setSubject('[ADMIN] Livraison réassignée');
            $adminWhatsappNotification->setTitle('Livraison réassignée');
            $adminWhatsappNotification->setBody('Une livraison a été réassignée à un nouveau livreur.');
            $adminWhatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
            $adminWhatsappNotification->setTarget($this->adminPhone);
            $adminWhatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
            
            // Données résumées pour WhatsApp
            $adminWhatsappNotification->setData([
                'Numéro' => $delivery->getTrackingNumber(),
                'Date' => $delivery->getDeliveryDate()->format('d/m/Y'),
                'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                'Raison' => substr($reassignmentMessage, 0, 100) . (strlen($reassignmentMessage) > 100 ? '...' : ''),
                'Nouveau livreur' => $deliveryPersonText,
                'Client' => $delivery->getCustomer() ? $delivery->getCustomer()->getFullname() : 'Non spécifié',
                'Destinataire' => $delivery->getRecipient() ? $delivery->getRecipient()->getFullname() : 'Non spécifié',
                'Statut' => $statusText
            ]);
            
            $this->entityManager->persist($adminWhatsappNotification);
            $this->messageBus->dispatch(new SendNotificationMessage($adminWhatsappNotification));
            
            // Envoyer un email au nouveau livreur si disponible
            if ($delivery->getDeliveryPerson() && $delivery->getDeliveryPerson()->getEmail()) {
                $deliveryPersonEmailNotification = new Notification();
                $deliveryPersonEmailNotification->setType(NotificationType::DELIVERY_ASSIGNED);
                $deliveryPersonEmailNotification->setSubject('Nouvelle livraison assignée');
                $deliveryPersonEmailNotification->setTitle('Nouvelle livraison assignée');
                $deliveryPersonEmailNotification->setBody("Bonjour, une livraison vous a été assignée. Veuillez consulter les détails ci-dessous.");
                $deliveryPersonEmailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
                $deliveryPersonEmailNotification->setTarget($delivery->getDeliveryPerson()->getEmail());
                $deliveryPersonEmailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
                
                // Données complètes pour le livreur
                $deliveryPersonEmailNotification->setData([
                    'Numéro de suivi' => $delivery->getTrackingNumber(),
                    'Date prévue' => $delivery->getDeliveryDate()->format('d/m/Y'),
                    'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                    'Statut' => $statusText,
                    'Raison de l\'assignation' => $reassignmentMessage,
                    'Description' => $delivery->getDescription() ?: 'Aucune description',
                    'Adresse de ramassage' => $pickupAddressText,
                    'Adresse de livraison' => $deliveryAddressText,
                    'Client' => $delivery->getCustomer() ? $delivery->getCustomer()->getFullname() . ' (' . ($delivery->getCustomer()->getPhone() ?: 'Pas de téléphone') . ')' : 'Non spécifié',
                    'Destinataire' => $delivery->getRecipient() ? $delivery->getRecipient()->getFullname() . ' (' . ($delivery->getRecipient()->getPhone() ?: 'Pas de téléphone') . ')' : 'Non spécifié',
                    'Informations supplémentaires' => $delivery->getAdditionalInformation() ?: 'Aucune'
                ]);
                
                $this->entityManager->persist($deliveryPersonEmailNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($deliveryPersonEmailNotification));
            }
            
            // Envoyer un message WhatsApp au nouveau livreur si disponible
            if ($delivery->getDeliveryPerson() && $delivery->getDeliveryPerson()->getPhone()) {
                $deliveryPersonWhatsappNotification = new Notification();
                $deliveryPersonWhatsappNotification->setType(NotificationType::DELIVERY_ASSIGNED);
                $deliveryPersonWhatsappNotification->setSubject('Nouvelle livraison assignée');
                $deliveryPersonWhatsappNotification->setTitle('Nouvelle livraison assignée');
                $deliveryPersonWhatsappNotification->setBody('Bonjour, une livraison vous a été assignée.');
                $deliveryPersonWhatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
                $deliveryPersonWhatsappNotification->setTarget($delivery->getDeliveryPerson()->getPhone());
                $deliveryPersonWhatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                
                // Données résumées pour WhatsApp
                $deliveryPersonWhatsappNotification->setData([
                    'Numéro' => $delivery->getTrackingNumber(),
                    'Date' => $delivery->getDeliveryDate()->format('d/m/Y'),
                    'Type' => $delivery->getType() === Delivery::TYPE_PACKAGE ? 'Colis' : 'Courrier',
                    'Raison' => substr($reassignmentMessage, 0, 100) . (strlen($reassignmentMessage) > 100 ? '...' : ''),
                    'Client' => $delivery->getCustomer() ? $delivery->getCustomer()->getFullname() : 'Non spécifié',
                    'Destinataire' => $delivery->getRecipient() ? $delivery->getRecipient()->getFullname() : 'Non spécifié',
                    'Adresse de ramassage' => substr($pickupAddressText, 0, 50) . (strlen($pickupAddressText) > 50 ? '...' : ''),
                    'Adresse de livraison' => substr($deliveryAddressText, 0, 50) . (strlen($deliveryAddressText) > 50 ? '...' : '')
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