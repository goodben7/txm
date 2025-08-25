<?php

namespace App\Command;

use App\Entity\AuthSession;
use App\Event\OtpSentEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TestOtpSentNotifierCommand extends Command
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('txm:test-otp-sent-notifier')
            ->setDescription('Test OTP sent notifier via event dispatcher');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Création d'une session d'authentification de test
        $authSession = new AuthSession();
        $authSession->setPhone('+243828120996'); // Utilisation d'un numéro de test
        $authSession->setOtpCode(rand(1000, 9999));
        $authSession->setCreatedAt(new \DateTimeImmutable('now'));
        $authSession->setExpiresAt(new \DateTimeImmutable('+5 minutes'));
        $authSession->setIsValidated(false);

        try {
            // Déclencher l'événement d'envoi d'OTP
            $event = new OtpSentEvent($authSession);
            $this->eventDispatcher->dispatch($event, OtpSentEvent::NAME);

            $io->success('Événement d\'envoi d\'OTP déclenché avec succès!');
            $io->note('Vérifiez que la notification a été envoyée à:');
            $io->listing([
                'WhatsApp: ' . $authSession->getPhone(),
            ]);

            $io->table(
                ['Information', 'Valeur'],
                [
                    ['Numéro de téléphone', $authSession->getPhone()],
                    ['Code OTP', $authSession->getOtpCode()],
                    ['Date de création', $authSession->getCreatedAt()->format('d/m/Y H:i')],
                    ['Date d\'expiration', $authSession->getExpiresAt()->format('d/m/Y H:i')],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}