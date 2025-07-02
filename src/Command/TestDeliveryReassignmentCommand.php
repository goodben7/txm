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

class TestDeliveryReassignmentCommand extends Command
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('txm:test-delivery-reassignment')
            ->setDescription('Test delivery reassignment notifications via event dispatcher');
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
        
        // Création du premier livreur (livreur initial)
        $initialDeliveryPerson = new DeliveryPerson();
        $initialDeliveryPerson->setFullname('Livreur Initial');
        $initialDeliveryPerson->setPhone('+243976839039');
        $initialDeliveryPerson->setEmail('sngoyi5@gmail.com');
        $initialDeliveryPerson->setStatus(DeliveryPerson::STATUS_ACTIVE);
        $initialDeliveryPerson->setVehicleType(DeliveryPerson::VEHICLE_TYPE_MOTORCYCLE);
        $initialDeliveryPerson->setCreatedAt(new \DateTimeImmutable('now'));
        
        // Création du nouveau livreur (livreur de remplacement)
        $newDeliveryPerson = new DeliveryPerson();
        $newDeliveryPerson->setFullname('Nouveau Livreur');
        $newDeliveryPerson->setPhone('+243976839039');
        $newDeliveryPerson->setEmail('sngoyi5@gmail.com');
        $newDeliveryPerson->setStatus(DeliveryPerson::STATUS_ACTIVE);
        $newDeliveryPerson->setVehicleType(DeliveryPerson::VEHICLE_TYPE_CAR);
        $newDeliveryPerson->setCreatedAt(new \DateTimeImmutable('now'));
        
        // Création d'une livraison de test
        $delivery = new Delivery();
        $delivery->setType(Delivery::TYPE_PACKAGE);
        $delivery->setDescription('Colis de test pour notification de réassignation');
        $delivery->setDeliveryDate(new \DateTimeImmutable('+3 days'));
        $delivery->setRecipient($recipient);
        $delivery->setCustomer($customer);
        $delivery->setCreatedAt(new \DateTimeImmutable('-1 day'));
        $delivery->setCreatedBy('SYSTEM');
        $delivery->setPickupAddress($pickupAddress);
        $delivery->setDeliveryAddress($deliveryAddress);
        $delivery->setAdditionalInformation('Informations supplémentaires pour le test de réassignation');
        $delivery->setTrackingNumber('PA-' . date('dmy') . date('Hi') . 'RAS');
        
        // Simuler une livraison validée et assignée au livreur initial
        $delivery->setStatus(Delivery::STATUS_VALIDATED);
        $delivery->setValidatedAt(new \DateTimeImmutable('-12 hours'));
        $delivery->setValidatedBy('SYSTEM');
        $delivery->setDeliveryPerson($initialDeliveryPerson);
        
        // Réassigner la livraison au nouveau livreur
        $delivery->setDeliveryPerson($newDeliveryPerson);
        $delivery->setReassignedAt(new \DateTimeImmutable('now'));
        $delivery->setReassignedBy('SYSTEM');
        
        // Définir un message explicatif de la réassignation
        $delivery->setMessage('Réassignation due à l\'indisponibilité du livreur initial');
        
        try {
            // Déclencher l'événement de réassignation de livraison
            $event = new ActivityEvent($delivery, Delivery::EVENT_DELIVERY_REASSIGNED);
            $this->eventDispatcher->dispatch($event, ActivityEvent::getEventName(Delivery::class, Delivery::EVENT_DELIVERY_REASSIGNED));
            
            $io->success('Événement de réassignation de livraison déclenché avec succès!');
            $io->note('Vérifiez que les notifications ont été envoyées à:');
            $io->listing([
                'Email client: ' . $customer->getEmail(),
                'WhatsApp client: ' . $customer->getPhone(),
                'Email destinataire: ' . $recipient->getEmail(),
                'WhatsApp destinataire: ' . $recipient->getPhone(),
                'Email ancien livreur: ' . $initialDeliveryPerson->getEmail(),
                'WhatsApp ancien livreur: ' . $initialDeliveryPerson->getPhone(),
                'Email nouveau livreur: ' . $newDeliveryPerson->getEmail(),
                'WhatsApp nouveau livreur: ' . $newDeliveryPerson->getPhone(),
                'Email administrateur: (configuré dans services.yaml)',
                'WhatsApp administrateur: (configuré dans services.yaml)'
            ]);
            
            $io->table(
                ['Information', 'Valeur'],
                [
                    ['Numéro de suivi', $delivery->getTrackingNumber()],
                    ['Statut', 'Validée'],
                    ['Date de livraison prévue', $delivery->getDeliveryDate()->format('d/m/Y')],
                    ['Ancien livreur', $initialDeliveryPerson->getFullname()],
                    ['Nouveau livreur', $newDeliveryPerson->getFullname()],
                    ['Contact du nouveau livreur', $newDeliveryPerson->getPhone()],
                    ['Raison de la réassignation', $delivery->getMessage()]
                ]
            );
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}