<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Address;
use App\Entity\OrderItem;
use App\Entity\Recipient;
use App\Entity\OrderItemOption;
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
     * Create a new order from the provided model
     * @param \App\Model\NewOrderModel $model The order data model
     * @throws \App\Exception\UnavailableDataException If there's an error during order creation
     * @throws \App\Exception\InvalidActionInputException If order items validation fails
     * @return Order The created order
     */
    public function createFrom(NewOrderModel $model): Order {
        // Get current user
        $user = $this->getCurrentUser();
        
        // Initialize new order with basic information
        $order = $this->initializeOrder($model, $user);
        
        // Process order items and validate them
        [$totalPrice, $store, $customer, $currency] = $this->processOrderItems($model->orderItems, $order, $model->selectedOptions);
        
        // Calculate and set delivery fee and total price
        $totalWithDelivery = $this->calculateTotalPrice($totalPrice, $currency, $order);
        
        // Set remaining order properties
        $order->setCustomer($customer);
        $order->setStore($store);
        $order->setTotalPrice((string)$totalWithDelivery);
        
        // Persist order and dispatch event
        $this->persistOrderAndNotify($order);
        
        return $order;
    }
    
    /**
     * Get the current authenticated user
     * @return User The current user
     */
    private function getCurrentUser(): User {
        $identifier = $this->security->getUser()->getUserIdentifier();
        return $this->userRepository->findByEmailOrPhone($identifier);
    }
    
    /**
     * Initialize a new order with basic information
     * @param NewOrderModel $model The order data model
     * @param User $user The current user
     * @return Order The initialized order
     */
    private function initializeOrder(NewOrderModel $model, User $user): Order {
        $order = new Order();
        
        $order->setStatus(Order::STATUS_PENDING);
        $order->setCreatedAt(new \DateTimeImmutable('now'));
        $order->setCreatedBy($user->getId());
        $order->setUserId($model->userId);
        $order->setDeliveryAddress($model->deliveryAddress);
        $order->setDescription($model->description);
        $order->setPickupAddress($model->pickupAddress);
        
        return $order;
    }
    
    /**
     * Process order items, validate them and add them to the order
     * @param array $orderItems The order items to process
     * @param Order $order The order to add items to
     * @param array $selectedOptions The selected options for each order item
     * @return array An array containing [totalPrice, store, customer, currency]
     * @throws InvalidActionInputException If validation fails
     */
    private function processOrderItems(array $orderItems, Order $order, array $selectedOptions = []): array {
        $totalPrice = 0;
        $store = null;
        $customer = null;
        $currency = null;
        $allowedCurrencies = ['USD', 'CDF'];
        
        foreach ($orderItems as $index => $item) {
            $currentStore = $item->getProduct()->getStore();
            $currentCurrency = $item->getProduct()->getCurrency();
            
            // Validate currency
            if (!in_array($currentCurrency, $allowedCurrencies)) {
                throw new InvalidActionInputException('Only USD and CDF currencies are allowed');
            }
            
            if ($index === 0) {
                // Initialize store and currency from first product
                $store = $currentStore;
                $customer = $currentStore->getCustomer();
                $currency = $currentCurrency;
            } else {
                // Ensure all products have the same currency
                if ($currency !== $currentCurrency) {
                    throw new InvalidActionInputException('All products in an order must have the same currency');
                }
                
                // Ensure all products come from the same store
                if ($store !== null && $currentStore->getId() !== $store->getId()) {
                    throw new InvalidActionInputException('All products in an order must come from the same store');
                }
            }
            
            $order->addOrderItem($item);
            $totalPrice += (float)$item->getUnitPrice() * $item->getQuantity();
            
            // Process selected options for this item if any
            if (isset($selectedOptions[$index]) && is_array($selectedOptions[$index])) {
                $this->processOrderItemOptions($item, $selectedOptions[$index]);
            }
        }
        
        return [$totalPrice, $store, $customer, $currency];
    }
    
    /**
     * Calculate the total price including delivery fee and product options price adjustments
     * @param float $totalPrice The subtotal price
     * @param string|null $currency The currency code
     * @param Order $order The order to set delivery fee on
     * @return float The total price including delivery fee and options price adjustments
     */
    private function calculateTotalPrice(float $totalPrice, ?string $currency, Order $order): float {
        // Calculate price adjustments from product options
        $optionAdjustments = 0;
        
        // Loop through all order items to calculate option price adjustments
        foreach ($order->getOrderItems() as $orderItem) {
            $quantity = $orderItem->getQuantity();
            
            // Calculate price adjustments based on the selected options only
            foreach ($orderItem->getOrderItemOptions() as $orderItemOption) {
                $optionValue = $orderItemOption->getOptionValue();
                if ($optionValue) {
                    $optionAdjustments += (float)$optionValue->getPriceAdjustment() * $quantity;
                }
            }
        }
        
        // Add option adjustments to total price
        $totalPrice += $optionAdjustments;
        
        // Set the subtotal (price of items before delivery fee and tax)
        $order->setSubtotal(number_format($totalPrice, 2, '.', ''));
        
        // Apply delivery fee based on currency
        $deliveryFee = $currency === 'CDF' ? Order::DELIVERY_FEE_CDF : Order::DELIVERY_FEE_USD;
        $order->setDeliveryFee($deliveryFee);
        
        // Calculate delivery tax (16% of delivery fee)
        $deliveryTax = (float)$deliveryFee * 0.16;
        $order->setDeliveryTax(number_format($deliveryTax, 2, '.', ''));
        
        // Calculate total with delivery fee and tax
        return $totalPrice + (float)$deliveryFee + $deliveryTax;
    }
    
    /**
     * Persist the order and dispatch creation event
     * @param Order $order The order to persist
     * @throws UnavailableDataException If persistence fails
     */
    private function persistOrderAndNotify(Order $order): void {
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
    }
    
    /**
     * Process selected options for an order item
     * @param OrderItem $orderItem The order item to add options to
     * @param array $optionValueIds Array of product option value IDs
     * @throws UnavailableDataException If an option value cannot be found
     */
    private function processOrderItemOptions(OrderItem $orderItem, array $optionValueIds): void {
        if (empty($optionValueIds)) {
            return;
        }
        
        // Get the repository for ProductOptionValue
        $optionValueRepository = $this->em->getRepository('App\\Entity\\ProductOptionValue');
        
        foreach ($optionValueIds as $optionValueId) {
            // Find the option value by ID
            $optionValue = $optionValueRepository->find($optionValueId);
            
            if (!$optionValue) {
                throw new UnavailableDataException("Product option value with ID {$optionValueId} not found");
            }
            
            // Create a new OrderItemOption and associate it with the order item
            $orderItemOption = new OrderItemOption();
            $orderItemOption->setOrderItem($orderItem);
            $orderItemOption->setOptionValue($optionValue);
            
            // Add the option to the order item
            $orderItem->addOrderItemOption($orderItemOption);
            
            // Persist the option
            $this->em->persist($orderItemOption);
        }
    }
    
    /**
     * Estimate an order without persisting it
     * @param \App\Model\NewOrderModel $model The order data model
     * @throws \App\Exception\InvalidActionInputException If order items validation fails
     * @return Order The estimated order with all price calculations
     */
    public function estimate(NewOrderModel $model): Order {
        // Get current user
        $user = $this->getCurrentUser();
        
        // Initialize new order with basic information
        $order = $this->initializeOrder($model, $user);
        
        // Process order items and validate them
        [$totalPrice, $store, $customer, $currency] = $this->processOrderItems($model->orderItems, $order, $model->selectedOptions);
        
        // Calculate and set delivery fee and total price
        $totalWithDelivery = $this->calculateTotalPrice($totalPrice, $currency, $order);
        
        // Set remaining order properties
        $order->setCustomer($customer);
        $order->setStore($store);
        $order->setTotalPrice((string)$totalWithDelivery);
        
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
                $createdFrom,
                $order->getStore()->getId()
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