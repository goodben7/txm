<?php

namespace App\EventSubscriber;

use App\Entity\Order;
use App\Entity\Notification;
use App\Entity\User;
use App\Event\ActivityEvent;
use App\Enum\NotificationType;
use App\Message\SendNotificationMessage;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderRejectionNotifier implements EventSubscriberInterface {

    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActivityEvent::getEventName(Order::class, Order::EVENT_ORDER_REJECTED) => 'onOrderRejection',
        ];
    }

    /**
     * Convertit le code de statut en texte lisible
     */
    private function getStatusText(string $status): string
    {
        return match($status) {
            Order::STATUS_PENDING => 'En attente',
            Order::STATUS_VALIDATED => 'Validée',
            Order::STATUS_IN_PROGRESS => 'En cours',
            Order::STATUS_COMPLETED => 'Terminée',
            Order::STATUS_REJECTED => 'Rejetée',
            default => 'Statut inconnu'
        };
    }

    public function onOrderRejection(ActivityEvent $event)
    {
        /** @var Order */
        $order = $event->getRessource();

        if (null === $order) {
            return;
        }

        // Récupérer la raison du rejet si disponible
        $rejectionReason = $event->getActivityDescription() ?? 'Aucune raison spécifiée';

        try {
            
            // Récupérer l'utilisateur si un userId est défini
            $user = null;
            if ($order->getUserId()) {
                $user = $this->userRepository->findOneBy(['id' => $order->getUserId()]);
            }
            
            // Envoyer un email au marchand (customer) si disponible
            if ($order->getCustomer()?->getEmail()) {
                $customerEmailNotification = new Notification();
                $customerEmailNotification->setType(NotificationType::SYSTEM_UPDATE);
                $customerEmailNotification->setSubject('Commande rejetée');
                $customerEmailNotification->setTitle('Commande rejetée');
                $customerEmailNotification->setBody("La commande numéro : " . ($order->getSerialNumber() ?? $order->getId()) . " a été rejetée.");
                $customerEmailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
                $customerEmailNotification->setTarget($order->getCustomer()->getEmail());
                $customerEmailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
                
                // Données minimales
                $customerEmailNotification->setData([
                    'Numéro de commande' => $order->getSerialNumber() ?? $order->getId(),
                    'Magasin' => $order->getStore() ? $order->getStore()->getLabel() : 'Non spécifié',
                    'Description' => $order->getDescription() ?: 'Aucune description',
                    'Statut' => $this->getStatusText($order->getStatus()),
                    'Date de rejet' => $order->getRejectedAt() ? $order->getRejectedAt()->format('d/m/Y H:i') : 'Non disponible',
                    'Raison du rejet' => $rejectionReason
                ]);
                
                $this->entityManager->persist($customerEmailNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($customerEmailNotification));
            }

            // Envoyer un message WhatsApp au marchand (customer) si disponible
            if ($order->getCustomer()?->getPhone()) {
                $customerWhatsappNotification = new Notification();
                $customerWhatsappNotification->setType(NotificationType::SYSTEM_UPDATE);
                $customerWhatsappNotification->setSubject('Commande rejetée');
                $customerWhatsappNotification->setTitle('Commande rejetée');
                $customerWhatsappNotification->setBody('La commande numéro : ' . ($order->getSerialNumber() ?? $order->getId()) . ' a été rejetée.');
                $customerWhatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
                $customerWhatsappNotification->setTarget($order->getCustomer()->getPhone());
                $customerWhatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                $customerWhatsappNotification->setData([
                    'Numéro de commande' => $order->getSerialNumber() ?? $order->getId(),
                    'Magasin' => $order->getStore() ? $order->getStore()->getLabel() : 'Non spécifié',
                    'Description' => $order->getDescription() ?: 'Aucune description',
                    'Statut' => $this->getStatusText($order->getStatus()),
                    'Date de rejet' => $order->getRejectedAt() ? $order->getRejectedAt()->format('d/m/Y H:i') : 'Non disponible',
                    'Raison du rejet' => $rejectionReason
                ]);
                $this->entityManager->persist($customerWhatsappNotification);
                $this->messageBus->dispatch(new SendNotificationMessage($customerWhatsappNotification));
            }
            
            // Envoyer des notifications à l'utilisateur client (userId) si disponible
            if ($order->getUserId()) {
                /** @var User|null $user */
                $user = $this->userRepository->findOneBy(['id' => $order->getUserId()]);
                
                if ($user?->getEmail()) {
                    $userEmailNotification = new Notification();
                    $userEmailNotification->setType(NotificationType::SYSTEM_UPDATE);
                    $userEmailNotification->setSubject('Votre commande a été rejetée');
                    $userEmailNotification->setTitle('Votre commande a été rejetée');
                    $userEmailNotification->setBody("Votre commande numéro : " . ($order->getSerialNumber() ?? $order->getId()) . " a été rejetée.");
                    $userEmailNotification->setSentVia(Notification::SENT_VIA_GMAIL);
                    $userEmailNotification->setTarget($user->getEmail());
                    $userEmailNotification->setTargetType(Notification::TARGET_TYPE_EMAIL);
                    
                    $userEmailNotification->setData([
                        'Numéro de commande' => $order->getSerialNumber() ?? $order->getId(),
                        'Magasin' => $order->getStore() ? $order->getStore()->getLabel() : 'Non spécifié',
                        'Description' => $order->getDescription() ?: 'Aucune description',
                        'Statut' => $this->getStatusText($order->getStatus()),
                        'Date de rejet' => $order->getRejectedAt() ? $order->getRejectedAt()->format('d/m/Y H:i') : 'Non disponible',
                        'Raison du rejet' => $rejectionReason
                    ]);
                    
                    $this->entityManager->persist($userEmailNotification);
                    $this->messageBus->dispatch(new SendNotificationMessage($userEmailNotification));
                }

                if ($user?->getPhone()) {
                    $userWhatsappNotification = new Notification();
                    $userWhatsappNotification->setType(NotificationType::SYSTEM_UPDATE);
                    $userWhatsappNotification->setSubject('Votre commande a été rejetée');
                    $userWhatsappNotification->setTitle('Votre commande a été rejetée');
                    $userWhatsappNotification->setBody('Votre commande numéro : ' . ($order->getSerialNumber() ?? $order->getId()) . ' a été rejetée.');
                    $userWhatsappNotification->setSentVia(Notification::SENT_VIA_WHATSAPP);
                    $userWhatsappNotification->setTarget($user->getPhone());
                    $userWhatsappNotification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
                    $userWhatsappNotification->setData([
                        'Numéro de commande' => $order->getSerialNumber() ?? $order->getId(),
                        'Magasin' => $order->getStore() ? $order->getStore()->getLabel() : 'Non spécifié',
                        'Description' => $order->getDescription() ?: 'Aucune description',
                        'Statut' => $this->getStatusText($order->getStatus()),
                        'Date de rejet' => $order->getRejectedAt() ? $order->getRejectedAt()->format('d/m/Y H:i') : 'Non disponible',
                        'Raison du rejet' => $rejectionReason
                    ]);
                    $this->entityManager->persist($userWhatsappNotification);
                    $this->messageBus->dispatch(new SendNotificationMessage($userWhatsappNotification));
                }
            }
            
            // Enregistrer toutes les notifications en base de données
            $this->entityManager->flush();
        }
        catch (\Exception $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
        }
    }  
}