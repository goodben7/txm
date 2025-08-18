<?php 

namespace App\MessageHandler\Command;

use App\Manager\StoreManager;
use Psr\Log\LoggerInterface;
use App\Message\Command\CreateStoreCommand;
use App\Message\Command\CommandHandlerInterface;

class CreateStoreCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private StoreManager $StoreManager
    )
    {
    }

    /**
     * Summary of __invoke
     * @param \App\Message\Command\CreateStoreCommand $command
     * @throws \Exception
     * @return mixed
     */
    public function __invoke(CreateStoreCommand $command)
    {
        try {
            return $this->StoreManager->createFrom($command);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new \Exception('Error in CreateStoreCommandHandler: ' . $e->getMessage(), 0, $e);
        }
    }
}