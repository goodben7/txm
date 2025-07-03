<?php 

namespace App\Command;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\Delivery;
use App\Entity\DeliveryPerson;
use App\Entity\Recipient;
use App\Event\ActivityEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestDeliveryTerminationCommand extends Command
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('txm:test-delivery-termination')
            ->setDescription('Test delivery termination notifications via event dispatcher');
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
        
        // Création d'un destinataire de test
        $recipient = new Recipient();
        $recipient->setFullname('Destinataire Test');
        $recipient->setPhone('+243852707296'); // Utilisation du même numéro que dans TestWhatsappCommand
        $recipient->setEmail('benbad54@gmail.com'); // Utilisation du même email que dans TestEmailCommand
        
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
        $deliveryAddress->setRecipient($recipient);
        
        // Création d'un livreur de test
        $deliveryPerson = new DeliveryPerson();
        $deliveryPerson->setFullname('Livreur Test');
        $deliveryPerson->setPhone('+243976839039');
        $deliveryPerson->setEmail('sngoyi5@gmail.com');
        $deliveryPerson->setStatus(DeliveryPerson::STATUS_ACTIVE);
        $deliveryPerson->setVehicleType(DeliveryPerson::VEHICLE_TYPE_MOTORCYCLE);
        $deliveryPerson->setCreatedAt(new \DateTimeImmutable('now'));
        
        // Création d'une livraison de test
        $delivery = new Delivery();
        $delivery->setType(Delivery::TYPE_PACKAGE);
        $delivery->setDescription('Colis de test pour notification de livraison terminée');
        $delivery->setDeliveryDate(new \DateTimeImmutable('+2 days'));
        $delivery->setRecipient($recipient);
        $delivery->setCustomer($customer);
        $delivery->setCreatedAt(new \DateTimeImmutable('-2 days'));
        $delivery->setCreatedBy('SYSTEM');
        $delivery->setPickupAddress($pickupAddress);
        $delivery->setDeliveryAddress($deliveryAddress);
        $delivery->setAdditionalInformation('Informations supplémentaires pour le test de livraison terminée');
        $delivery->setTrackingNumber('PA-' . date('dmy') . date('Hi') . 'TRM');
        
        // Simuler le parcours complet de la livraison
        // 1. Validation
        $delivery->setStatus(Delivery::STATUS_VALIDATED);
        $delivery->setValidatedAt(new \DateTimeImmutable('-1 day'));
        $delivery->setValidatedBy('SYSTEM');
        $delivery->setDeliveryPerson($deliveryPerson);
        
        // 2. Ramassage
        $delivery->setStatus(Delivery::STATUS_PICKUPED);
        $delivery->setPickupedAt(new \DateTimeImmutable('-12 hours'));
        $delivery->setPickupedBy('SYSTEM');
        
        // 3. En cours
        $delivery->setStatus(Delivery::STATUS_INPROGRESS);
        $delivery->setInProgressAt(new \DateTimeImmutable('-6 hours'));
        $delivery->setInProgressBy('SYSTEM');
        
        // 4. Terminée
        $delivery->setStatus(Delivery::STATUS_TERMINATED);
        $delivery->setTerminedAt(new \DateTimeImmutable('now'));
        $delivery->setTerminedBy('SYSTEM');
        
        try {
            // Déclencher l'événement de livraison terminée
            $event = new ActivityEvent($delivery, Delivery::EVENT_DELIVERY_TERMINATED);
            $this->eventDispatcher->dispatch($event, ActivityEvent::getEventName(Delivery::class, Delivery::EVENT_DELIVERY_TERMINATED));
            
            $io->success('Événement de livraison terminée déclenché avec succès!');
            $io->note('Vérifiez que les notifications ont été envoyées à:');
            $io->listing([
                'Email client: ' . $customer->getEmail(),
                'WhatsApp client: ' . $customer->getPhone(),
                'Email destinataire: ' . $recipient->getEmail(),
                'WhatsApp destinataire: ' . $recipient->getPhone(),
                'Email livreur: ' . $deliveryPerson->getEmail(),
                'WhatsApp livreur: ' . $deliveryPerson->getPhone(),
                'Email administrateur: (configuré dans services.yaml)',
                'WhatsApp administrateur: (configuré dans services.yaml)'
            ]);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}