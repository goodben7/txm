<?php 

namespace App\Command;

use App\Entity\Customer;
use App\Entity\Document;
use App\Event\ActivityEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestDocumentRejectionCommand extends Command
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('txm:test-document-rejection')
            ->setDescription('Test document rejection notifications via event dispatcher');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Création d'un client de test avec un ID simulé
        $customer = new Customer();
        $customer->setCompanyName('Société Test');
        $customer->setFullname('Client Test');
        $customer->setPhone('0828120996'); // Utilisation du même numéro que dans TestWhatsappCommand
        $customer->setEmail('bmukena85@gmail.com'); // Utilisation du même email que dans TestEmailCommand
        $customer->setCreatedAt(new \DateTimeImmutable('-30 days')); // Date de création simulée
        $customer->setIsActivated(true);
        $customer->setIsVerified(true);
        
        $this->entityManager->persist($customer);
        
        // Création d'un document de test
        $document = new Document();
        $document->setType(Document::TYPE_ID); // Pièce d'identité
        $document->setTitle('Carte d\'identité de test');
        $document->setStatus(Document::STATUS_REFUSED); // Statut refusé
        $document->setHolderId($customer->getId()); // Utilisation de l'ID du client simulé
        $document->setRejectionReason('Document illisible ou incomplet');
        $document->setRejectedAt(new \DateTimeImmutable('now'));
        $document->setRejectedBy('SYSTEM');
        $document->setUploadedAt(new \DateTimeImmutable('-1 day')); // Document uploadé hier
        
        try {
            // Persister le client dans la base de données pour que le notifier puisse le trouver
            $io->info('Enregistrement temporaire du client et du document dans la base de données...');
            $this->entityManager->persist($customer);
            $this->entityManager->persist($document);
            $this->entityManager->flush();
            
            // Déclencher l'événement de rejet de document
            $event = new ActivityEvent($document, Document::EVENT_DOCUMENT_REJECT);
            $this->eventDispatcher->dispatch($event, ActivityEvent::getEventName(Document::class, Document::EVENT_DOCUMENT_REJECT));
            
            $io->success('Événement de rejet de document déclenché avec succès!');
            $io->note('Vérifiez que les notifications ont été envoyées à:');
            $io->listing([
                'Email client: ' . $customer->getEmail(),
                'WhatsApp client: ' . $customer->getPhone(),
            ]);
            
            $io->table(
                ['Information', 'Valeur'],
                [
                    ['Type de document', Document::getTypeAsChoices()[$document->getType()] ?? $document->getType()],
                    ['Titre', $document->getTitle()],
                    ['Statut', 'Refusé'],
                    ['Raison du rejet', $document->getRejectionReason()],
                    ['Date de rejet', $document->getRejectedAt()->format('d/m/Y H:i')],
                    ['Rejeté par', $document->getRejectedBy()],
                    ['ID du détenteur', $document->getHolderId()]
                ]
            );
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        } finally {
            // Supprimer le client et le document de la base de données après l'envoi de la notification
            $io->info('Suppression des données temporaires de la base de données...');
            
            if (isset($document)) {
                $this->entityManager->remove($document);
            }
            
            if (isset($customer) && $customer->getId()) {
                $this->entityManager->remove($customer);
            }
            
            $this->entityManager->flush();
        }
    }
}