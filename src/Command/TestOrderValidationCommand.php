<?php 

namespace App\Command;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\Store;
use App\Event\ActivityEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestOrderValidationCommand extends Command
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('txm:test-order-validation')
            ->setDescription('Test order validation notifications via event dispatcher');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Création d'un client de test
        $customer = new Customer();
        $customer->setCompanyName('Société Test');
        $customer->setFullname('Client Test');
        $customer->setPhone('+243828120996'); // Utilisation du même numéro que dans TestWhatsappCommand
        $customer->setEmail('bmukena85@gmail.com'); // Utilisation du même email que dans TestEmailCommand
        
        // Création d'un magasin de test
        $store = new Store();
        $store->setLabel('Magasin Test');
        $store->setDescription('Un magasin pour tester les notifications de commande');
        $store->setEmail('store@example.com');
        $store->setPhone('+243123456789');
        $store->setCustomer($customer);
        
        // Création d'un produit de test
        $product1 = new Product();
        $product1->setName('Produit Test 1');
        $product1->setDescription('Description du produit test 1');
        $product1->setPrice('15.99');
        $product1->setStore($store);
        
        $product2 = new Product();
        $product2->setName('Produit Test 2');
        $product2->setDescription('Description du produit test 2');
        $product2->setPrice('25.50');
        $product2->setStore($store);
        
        // Création d'une adresse de ramassage
        $pickupAddress = new Address();
        $pickupAddress->setLabel('Adresse de ramassage');
        $pickupAddress->setAddress('123 Rue de Ramassage, Kinshasa');
        $pickupAddress->setIsMain(true);
        $pickupAddress->setCustomer($customer);
        
        // Création d'une adresse de livraison
        $deliveryAddress = new Address();
        $deliveryAddress->setLabel('Adresse de livraison');
        $deliveryAddress->setAddress('456 Avenue de Livraison, Kinshasa');
        $deliveryAddress->setIsMain(true);
        
        // Création d'une commande de test
        $order = new Order();
        $order->setStatus(Order::STATUS_VALIDATED); // Statut validé
        $order->setCreatedAt(new \DateTimeImmutable('-1 day')); // Créée hier
        $order->setCreatedBy('SYSTEM');
        $order->setValidatedAt(new \DateTimeImmutable('now')); // Validée maintenant
        $order->setValidatedBy('SYSTEM'); // Validée par le système
        $order->setTotalPrice('41.49'); // Somme des produits
        $order->setCustomer($customer);
        $order->setStore($store);
        $order->setUserId('USGPTB0912121406'); // ID utilisateur fictif
        $order->setPickupAddress($pickupAddress);
        $order->setDeliveryAddress($deliveryAddress);
        $order->setDescription('Commande de test pour notification de validation');
        $order->setSerialNumber(date('ymd') . date('Hi')); // Numéro de série basé sur la date
        
        // Ajout des éléments de commande
        $orderItem1 = new OrderItem();
        $orderItem1->setProduct($product1);
        $orderItem1->setQuantity(1);
        $orderItem1->setUnitPrice('15.99');
        $order->addOrderItem($orderItem1);
        
        $orderItem2 = new OrderItem();
        $orderItem2->setProduct($product2);
        $orderItem2->setQuantity(1);
        $orderItem2->setUnitPrice('25.50');
        $order->addOrderItem($orderItem2);
        
        try {
            // Déclencher l'événement de validation de commande
            $event = new ActivityEvent($order, Order::EVENT_ORDER_VALIDATED);
            $this->eventDispatcher->dispatch($event, ActivityEvent::getEventName(Order::class, Order::EVENT_ORDER_VALIDATED));
            
            $io->success('Événement de validation de commande déclenché avec succès!');
            $io->note('Vérifiez que les notifications ont été envoyées à:');
            $io->listing([
                'Email client: ' . $customer->getEmail(),
                'WhatsApp client: ' . $customer->getPhone(),
                'Email utilisateur: (si configuré dans la base de données pour USGPTB0912121406)',
                'WhatsApp utilisateur: (si configuré dans la base de données pour USGPTB0912121406)'
            ]);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}