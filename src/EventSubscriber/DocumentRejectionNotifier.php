<?php

namespace App\EventSubscriber;

use App\Entity\Document;
use App\Entity\Notification;
use App\Entity\Customer;
use App\Event\ActivityEvent;
use App\Enum\NotificationType;
use App\Message\SendNotificationMessage;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DocumentRejectionNotifier implements EventSubscriberInterface {

    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private CustomerRepository $customerRepository
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActivityEvent::getEventName(Document::class, Document::EVENT_DOCUMENT_REJECT) => 'onDocumentRejection',
        ];
    }

    public function onDocumentRejection(ActivityEvent $event)
    {
        /** @var Document */
        $document = $event->getRessource();

        if (null === $document) {
            return;
        }

        try {
            // Récupérer la raison du rejet
            $rejectionReason = $document->getRejectionReason() ?: 'Aucune raison spécifiée';
            
            // Récupérer le customer associé au document
            /** @var Customer $customer */
            $customer = $this->customerRepository->find($document->getHolderId());
            
            if (!$customer) {
                $this->logger->error('Customer not found for document rejection notification', [
                    'document_id' => $document->getId(),
                    'holder_id' => $document->getHolderId()
                ]);
                return;
            }
            
            // Créer une notification email
            $emailNotification = new Notification();
            $emailNotification->setType(NotificationType::SYSTEM_UPDATE); // Utiliser un type existant approprié
            $emailNotification->setSubject('Document rejeté');
            $emailNotification->setTitle('Document rejeté');
            $emailNotification->setBody("Votre document a été rejeté. Veuillez consulter les détails ci-dessous.");
            $emailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
            
            // Placer toutes les informations détaillées dans data
            $emailNotification->setData([
                'Type de document' => Document::getTypeAsChoices()[$document->getType()] ?? $document->getType(),
                'Titre' => $document->getTitle() ?: 'Sans titre',
                'Raison du rejet' => $rejectionReason,
                'Date de rejet' => $document->getRejectedAt()->format('d/m/Y H:i'),
                'Instructions' => 'Veuillez soumettre un nouveau document en tenant compte de la raison du rejet.'
            ]);
            
            // Envoyer un email au client si disponible
            if ($customer->getEmail()) {
                $customerEmailNotification = clone $emailNotification;
                $customerEmailNotification->setTarget($customer->getEmail());
                $customerEmailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
                $this->entityManager->persist($customerEmailNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($customerEmailNotification));
            }

            // Envoyer un message WhatsApp au client si disponible
            if ($customer->getPhone()) {
                $customerWhatsappNotification = new Notification();
                $customerWhatsappNotification->setType(NotificationType::SYSTEM_UPDATE); // Utiliser un type existant approprié
                $customerWhatsappNotification->setSubject('Document rejeté');
                $customerWhatsappNotification->setTitle('Document rejeté');
                $customerWhatsappNotification->setBody('Votre document a été rejeté.');
                $customerWhatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
                $customerWhatsappNotification->setTarget($customer->getPhone());
                $customerWhatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                $customerWhatsappNotification->setData([
                    'Type de document' => Document::getTypeAsChoices()[$document->getType()] ?? $document->getType(),
                    'Titre' => $document->getTitle() ?: 'Sans titre',
                    'Raison du rejet' => $rejectionReason,
                    'Date de rejet' => $document->getRejectedAt()->format('d/m/Y H:i'),
                    'Instructions' => 'Veuillez soumettre un nouveau document en tenant compte de la raison du rejet.'
                ]);
                $this->entityManager->persist($customerWhatsappNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($customerWhatsappNotification));
            }
            
            $this->logger->info('Document rejection notification sent', [
                'document_id' => $document->getId(),
                'customer_id' => $customer->getId()
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to send document rejection notification', [
                'document_id' => $document->getId(),
                'error' => $e->getMessage(),
                'exception' => $e
            ]);
        }
    }
}