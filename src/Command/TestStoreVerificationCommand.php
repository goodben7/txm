<?php

namespace App\Command;

use App\Entity\Customer;
use App\Entity\Service;
use App\Entity\Store;
use App\Enum\ServiceType;
use App\Event\ActivityEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestStoreVerificationCommand extends Command
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('txm:test-store-verification')
            ->setDescription('Test store verification notifications via event dispatcher');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Création d'un client de test
        $customer = new Customer();
        $customer->setCompanyName('Société de Test');
        $customer->setFullname('Client de Test');
        $customer->setPhone('0828120996'); // Numéro pour WhatsApp
        $customer->setEmail('bmukena85@gmail.com'); // Email pour notification
        $customer->setCreatedAt(new \DateTimeImmutable('-15 days'));
        $customer->setIsActivated(true);
        $customer->setIsVerified(true);

        // Création d'un service de test
        $service = new Service();
        $service->setName('Service de Test');
        $service->setType(ServiceType::GROCERY);
        $service->setActive(true);

        // Création d'une boutique de test
        $store = new Store();
        $store->setLabel('Boutique de Test');
        $store->setCustomer($customer);
        $store->setService($service);
        $store->setActive(true);
        $store->setCreatedAt(new \DateTimeImmutable('-15 days'));
        $store->setIsVerified(true); // Simuler la validation
        //$store->setValidatedAt(new \DateTimeImmutable('now'));

        try {
            $io->info('Enregistrement temporaire du client et de la boutique...');
            $this->entityManager->persist($customer);
            $this->entityManager->persist($service);
            $this->entityManager->persist($store);
            $this->entityManager->flush();

            // Déclencher l'événement de vérification de la boutique
            $event = new ActivityEvent($store, Store::EVENT_STORE_VERIFIED);
            $this->eventDispatcher->dispatch($event, ActivityEvent::getEventName(Store::class, Store::EVENT_STORE_VERIFIED));

            $io->success('Événement de vérification de boutique déclenché avec succès!');
            $io->note('Vérifiez que les notifications ont été envoyées à:');
            $io->listing([
                'Email client: ' . $customer->getEmail(),
                'WhatsApp client: ' . $customer->getPhone(),
            ]);

            $io->table(
                ['Information', 'Valeur'],
                [
                    ['Boutique', $store->getLabel()],
                    ['Client', $customer->getFullname()],
                    ['Statut', $store->getIsVerified() ? 'Validée' : 'Non validée'],
                    //['Date de validation', $store->getValidatedAt()->format('d/m/Y H:i')],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        } finally {
            $io->info('Suppression des données temporaires...');
            if ($this->entityManager->contains($store)) {
                $this->entityManager->remove($store);
            }
            if ($this->entityManager->contains($service)) {
                $this->entityManager->remove($service);
            }
            if ($this->entityManager->contains($customer)) {
                $this->entityManager->remove($customer);
            }
            $this->entityManager->flush(); 
        }
    }
}