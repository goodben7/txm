<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Address;
use App\Entity\Recipient;
use App\Model\NewOrderModel;
use App\Model\NewDeliveryModel;
use App\Repository\UserRepository;
use App\Repository\RecipientRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use Symfony\Bundle\SecurityBundle\Security;
use App\Exception\InvalidActionInputException;

class OrderManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private UserRepository $userRepository,
        private RecipientRepository $recipientRepository,
        private ActivityEventDispatcher $eventDispatcher,
        private DeliveryManager $deliveryManager
    ){
    }
    
    /**
     * Summary of createFrom
     * @param \App\Model\NewOrderModel $model
     * @throws \App\Exception\UnavailableDataException
     * @return Order
     */
    public function createFrom(NewOrderModel $model): Order {

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $order = new Order();
        
        $order->setStatus(Order::STATUS_PENDING);
        $order->setCreatedAt(new \DateTimeImmutable('now'));
        $order->setCreatedBy($user->getId());
        $order->setUserId($model->userId);
        $order->setDeliveryAddress($model->deliveryAddress);
        
        $totalPrice = 0;
        $store = null;
        $customer = null;
        
        foreach ($model->orderItems as $index => $item) {
            // Vérifier que tous les produits proviennent du même magasin
            $currentStore = $item->getProduct()->getStore();
            
            if ($index === 0) {
                // Premier produit, on initialise le magasin de référence
                $store = $currentStore;
                $customer = $currentStore->getCustomer();
            } else if ($store !== null && $currentStore->getId() !== $store->getId()) {
                // Si un produit provient d'un magasin différent, on lance une exception
                throw new InvalidActionInputException('All products in an order must come from the same store');
            }
            
            $order->addOrderItem($item);
            $totalPrice += (float)$item->getUnitPrice() * $item->getQuantity();
        }
        
        $order->setCustomer($customer);
        $order->setStore($store);
        $order->setTotalPrice((string)$totalPrice);
        
        try {
            $this->em->persist($order);
            $this->em->flush();
            
            $this->eventDispatcher->dispatch(
                $order, 
                Order::EVENT_ORDER_CREATED, 
                null, 
                null
            );

        } catch (\Exception $e) {
            throw new UnavailableDataException($e->getMessage());
        }
        
        return $order;
    }

    /**
     * Validate an order and assign it to a delivery person
     * @param Order $order The order to validate
     * @throws InvalidActionInputException If the order status is not valid for validation
     * @return Order The validated order
     */
    public function validate(Order $order) : Order
    {
        if ($order->getStatus() !== Order::STATUS_PENDING) {
            throw new InvalidActionInputException('Action not allowed : invalid order state'); 
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $order->setStatus(Order::STATUS_VALIDATED);
        $order->setValidatedAt(new \DateTimeImmutable('now'));
        $order->setValidatedBy($user->getId());

        $this->em->persist($order);
        $this->em->flush();

        $this->eventDispatcher->dispatch(
            $order, 
            Order::EVENT_ORDER_VALIDATED, 
            null, 
            null)
        ;

        return $order; 
    }

    /**
     * Reject an order with a reason
     * @param Order $order The order to reject
     * @param string $rejectionReason The reason for rejection
     * @throws InvalidActionInputException If the order status is not valid for rejection
     * @return Order The rejected order
     */
    public function reject(Order $order, string $rejectionReason) : Order
    {
        if (!in_array($order->getStatus(), [Order::STATUS_PENDING, Order::STATUS_VALIDATED, Order::STATUS_IN_PROGRESS])) {
            throw new InvalidActionInputException('Action not allowed : invalid order state');
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $order->setStatus(Order::STATUS_REJECTED);
        $order->setRejectedAt(new \DateTimeImmutable('now'));
        $order->setRejectedBy($user->getId());
        

        $this->em->persist($order);
        $this->em->flush();
            
        $this->eventDispatcher->dispatch(
            $order, 
            Order::EVENT_ORDER_REJECTED , 
            null, 
            $rejectionReason
        );

        return $order;
    }

    /**
     * Set an order to in progress status
     * @param Order $order The order to set in progress
     * @throws InvalidActionInputException If the order status is not valid for setting in progress
     * @return Order The order set to in progress
     */
    public function inprogress(Order $order) : Order
    {
        if($order->getStatus() != Order::STATUS_VALIDATED){
            throw new InvalidActionInputException('Action not allowed : invalid order state'); 
        }

        $identifier = $this->security->getUser()->getUserIdentifier();

        /** @var User|null $user */
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        $order->setStatus(Order::STATUS_IN_PROGRESS);
        $order->setInProgressAt(new \DateTimeImmutable('now'));
        $order->setInProgressBy($user->getId());
    

        $this->em->persist($order);
        $this->em->flush();
            
        $this->eventDispatcher->dispatch(
            $order, 
            Order::EVENT_ORDER_INPROGRESS , 
            null, 
            null
        );

        return $order; 
    }

    /**
     * Finish an order and mark it as completed
     * @param Order $order The order to finish
     * @param Address|null $pickupAddress Optional pickup address to set
     * @param string|null $description Optional description to set
     * @throws InvalidActionInputException If the order status is not valid for finishing
     * @return Order The completed order
     */
    public function finish(Order $order, string $type, ?\DateTimeImmutable $deliveryDate, ?Address $pickupAddress = null, ?string $description = null, ?string $createdFrom = null) : Order
    {
        if($order->getStatus() != Order::STATUS_IN_PROGRESS){
            throw new InvalidActionInputException('Action not allowed : invalid order state'); 
        }

        try {
            $recipient = $this->recipientRepository->findOneByUserIdAndCustomer($order->getUserId(), $order->getCustomer());
            
            if ($recipient === null) {

                /** @var User|null $user */
                $user = $this->userRepository->findOneBy(['id' => $order->getUserId()]);

                if ($user === null) {
                    throw new InvalidActionInputException('User not found');
                }
                else {

                    $recipient = new Recipient();
                    $recipient->setUserId($order->getUserId());
                    $recipient->setCustomer($order->getCustomer());
                    $recipient->setFullname($user->getDisplayName());
                    $recipient->setPhone($user->getPhone());
                    $recipient->setEmail($user->getEmail());
                    $recipient->setCreatedAt(new \DateTimeImmutable('now'));

                    $this->em->persist($recipient);
                    $this->em->flush();
                }

            }

            $model = new NewDeliveryModel(
                $type, 
                $description, 
                $deliveryDate, 
                $recipient, 
                $order->getCustomer(),
                $pickupAddress,
                $order->getDeliveryAddress(),
                $description,
                $createdFrom
            );

            $delivery = $this->deliveryManager->createFrom($model, false);
            $order->setDelivery($delivery);
        
            $identifier = $this->security->getUser()->getUserIdentifier();

            /** @var User|null $user */
            $user = $this->userRepository->findByEmailOrPhone($identifier);

            $order->setStatus(Order::STATUS_COMPLETED);
            $order->setTerminedAt(new \DateTimeImmutable('now'));
            $order->setTerminedBy($user->getId());
            $order->setPickupAddress($pickupAddress);
            $order->setDescription($description);

            $this->em->persist($order);
            $this->em->flush();


            $this->eventDispatcher->dispatch(
                $order, 
                Order::EVENT_ORDER_TERMINATED, 
                null, 
                null
            );

            return $order;
        } catch (\Exception $e) {
            throw new UnavailableDataException('Error while finishing order: ' . $e->getMessage());
        }
    }
}