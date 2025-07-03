<?php 

namespace App\Command;

use App\Entity\Notification;
use App\Message\SendNotificationMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TestEmailCommand extends Command
{
    public function __construct(private MessageBusInterface $messageBus)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('txm:test-email')
            ->setDescription('Test sending Email notification via message bus');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notification = new Notification();
        $notification->setTarget('bmukena85@gmail.com');
        $notification->setTitle('Test message via message bus');
        $notification->setBody('Test message via message bus');
        $notification->setSubject('Test message via message bus');
        $notification->setTargetType(Notification::TARGET_TYPE_EMAIL);
        $notification->setSentVia(Notification::SENT_VIA_GMAIL);

        try {
            $message = new SendNotificationMessage($notification);
            $this->messageBus->dispatch($message);
            
            $output->writeln('Notification message dispatched successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error: '.$e->getMessage().'</error>');
            return Command::FAILURE;
        }
    }
}