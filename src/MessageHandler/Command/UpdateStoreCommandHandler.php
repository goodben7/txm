<?php 

namespace App\MessageHandler\Command;

use App\Manager\StoreManager;
use Psr\Log\LoggerInterface;
use App\Message\Command\UpdateStoreCommand;
use App\Message\Command\CommandHandlerInterface;

class UpdateStoreCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private StoreManager $StoreManager
    )
    {
    }

    /**
     * Summary of __invoke
     * @param \App\Message\Command\UpdateStoreCommand $command
     * @throws \Exception
     * @return mixed
     */
    public function __invoke(UpdateStoreCommand $command)
    {
        try {
            return $this->StoreManager->updateFrom($command, $command->StoreId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new \Exception('Error in UpdateStoreCommandHandler: ' . $e->getMessage(), 0, $e);
        }
    }
}