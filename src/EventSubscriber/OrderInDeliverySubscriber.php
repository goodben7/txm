<?php

namespace App\EventSubscriber;

use App\Entity\Order;
use App\Entity\Delivery;
use App\Event\ActivityEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderInDeliverySubscriber implements EventSubscriberInterface {

    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActivityEvent::getEventName(Delivery::class, Delivery::EVENT_DELIVERY_INPROGRESS) => 'onDeliveryInProgress',
        ];
    }

    public function onDeliveryInProgress(ActivityEvent $event)
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
                // Mettre à jour le statut de la commande à "En livraison"
                $order->setStatus(Order::STATUS_IN_DELIVERY);
                
                $this->entityManager->persist($order);
                $this->entityManager->flush();
                
                $this->logger->info('Order status updated to IN_DELIVERY', [
                    'order_id' => $order->getId(),
                    'delivery_id' => $delivery->getId()
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error updating order status to IN_DELIVERY', [
                'error' => $e->getMessage(),
                'delivery_id' => $delivery->getId()
            ]);
        }
    }
}