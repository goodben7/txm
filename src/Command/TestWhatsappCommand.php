<?php 

namespace App\Command;

use App\Entity\Notification;
use App\Message\SendNotificationMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TestWhatsappCommand extends Command
{
    public function __construct(private MessageBusInterface $messageBus)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('txm:test-whatsapp')
            ->setDescription('Test sending Message notification via message bus');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notification = new Notification();
        $notification->setTarget('+243828120996');
        $notification->setBody('Test message via message bus');
        $notification->setTargetType(Notification::TARGET_TYPE_WHATSAPP);
        $notification->setSentVia(Notification::SENT_VIA_WHATSAPP);

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