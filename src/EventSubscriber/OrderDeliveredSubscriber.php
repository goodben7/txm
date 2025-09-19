<?php

namespace App\EventSubscriber;

use App\Entity\Order;
use App\Entity\Delivery;
use App\Event\ActivityEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderDeliveredSubscriber implements EventSubscriberInterface {

    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActivityEvent::getEventName(Delivery::class, Delivery::EVENT_DELIVERY_TERMINATED) => 'onDeliveryTerminated',
        ];
    }

    public function onDeliveryTerminated(ActivityEvent $event)
    {
        /** @var Delivery */
        $delivery = $event->getRessource();

        if (null === $delivery) {
            return;
        }

        try {
            // Récupérer la commande associée à la livraison
            $order = $delivery->getRelatedOrder();
            
            if ($order) {
                // Mettre à jour le statut de la commande à "Livrée"
                $order->setStatus(Order::STATUS_DELIVERED);
                
                $this->entityManager->persist($order);
                $this->entityManager->flush();
                
                $this->logger->info('Order status updated to DELIVERED', [
                    'order_id' => $order->getId(),
                    'delivery_id' => $delivery->getId()
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error updating order status to DELIVERED', [
                'error' => $e->getMessage(),
                'delivery_id' => $delivery->getId()
            ]);
        }
    }
}