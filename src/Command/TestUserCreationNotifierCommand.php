<?php 

namespace App\Command;

use App\Entity\User;
use App\Event\ActivityEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestUserCreationNotifierCommand extends Command
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('txm:test-user-creation-notifier')
            ->setDescription('Test user creation notifications via event dispatcher');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Création d'un utilisateur de test
        $user = new User();
        $user->setEmail('bmukena85@gmail.com'); // Utilisation du même email que dans TestEmailCommand
        $user->setPhone('0828120996'); // Utilisation du même numéro que dans TestWhatsappCommand
        $user->setDisplayName('Utilisateur Test');
        $user->setPlainPassword('MotDePasseTest123'); // Mot de passe temporaire pour le test
        $user->setCreatedAt(new \DateTimeImmutable('now'));
        $user->setLocked(false);
        
        try {
            // Déclencher l'événement de création d'utilisateur
            $event = new ActivityEvent($user, User::EVENT_USER_CREATED);
            $this->eventDispatcher->dispatch($event, ActivityEvent::getEventName(User::class, User::EVENT_USER_CREATED));
            
            $io->success('Événement de création d\'utilisateur déclenché avec succès!');
            $io->note('Vérifiez que les notifications ont été envoyées à:');
            $io->listing([
                'Email: ' . $user->getEmail(),
                'WhatsApp: ' . $user->getPhone(),
            ]);
            
            $io->table(
                ['Information', 'Valeur'],
                [
                    ['Identifiant', $user->getEmail()],
                    ['Nom', $user->getDisplayName()],
                    ['Téléphone', $user->getPhone()],
                    ['Date d\'inscription', $user->getCreatedAt()->format('d/m/Y H:i')],
                    ['Mot de passe temporaire', $user->getPlainPassword()]
                ]
            );
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}