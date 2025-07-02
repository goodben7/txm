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

class TestDeliveryValidationCommand extends Command
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('txm:test-delivery-validation')
            ->setDescription('Test delivery validation notifications via event dispatcher');
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
        $delivery->setDescription('Colis de test pour notification de validation');
        $delivery->setDeliveryDate(new \DateTimeImmutable('+2 days'));
        $delivery->setRecipient($recipient);
        $delivery->setCustomer($customer);
        $delivery->setCreatedAt(new \DateTimeImmutable('now'));
        $delivery->setCreatedBy('SYSTEM');
        $delivery->setPickupAddress($pickupAddress);
        $delivery->setDeliveryAddress($deliveryAddress);
        $delivery->setAdditionalInformation('Informations supplémentaires pour le test de validation');
        $delivery->setTrackingNumber('PA-' . date('dmy') . date('Hi') . 'VAL');
        $delivery->setStatus(Delivery::STATUS_VALIDATED); // Statut validé
        $delivery->setDeliveryPerson($deliveryPerson); // Assignation du livreur
        $delivery->setValidatedAt(new \DateTimeImmutable('now')); // Date de validation
        $delivery->setValidatedBy('SYSTEM'); // Validé par le système
        
        try {
            // Déclencher l'événement de validation de livraison
            $event = new ActivityEvent($delivery, Delivery::EVENT_DELIVERY_VALIDATED);
            $this->eventDispatcher->dispatch($event, ActivityEvent::getEventName(Delivery::class, Delivery::EVENT_DELIVERY_VALIDATED));
            
            $io->success('Événement de validation de livraison déclenché avec succès!');
            $io->note('Vérifiez que les notifications ont été envoyées à:');
            $io->listing([
                'Email client: ' . $customer->getEmail(),
                'WhatsApp client: ' . $customer->getPhone(),
                'Email destinataire: ' . $recipient->getEmail(),
                'WhatsApp destinataire: ' . $recipient->getPhone(),
                'Email administrateur: (configuré dans services.yaml)',
                'WhatsApp administrateur: (configuré dans services.yaml)'
            ]);
            
            $io->table(
                ['Information', 'Valeur'],
                [
                    ['Numéro de suivi', $delivery->getTrackingNumber()],
                    ['Statut', 'Validée'],
                    ['Livreur assigné', $deliveryPerson->getFullname()],
                    ['Contact du livreur', $deliveryPerson->getPhone()]
                ]
            );
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}