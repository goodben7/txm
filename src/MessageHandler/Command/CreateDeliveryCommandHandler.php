<?php 

namespace App\MessageHandler\Command;

use App\Entity\DeliveryModel;
use App\Entity\Recipient;
use Psr\Log\LoggerInterface;
use App\Model\NewDeliveryModel;
use App\Manager\DeliveryManager;
use App\Repository\RecipientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Message\Command\CommandBusInterface;
use App\Message\Command\CreateDeliveryCommand;
use App\Message\Command\CommandHandlerInterface;
use App\Repository\AddressRepository;

class CreateDeliveryCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
        private ManagerRegistry $managerRegistry,
        private CommandBusInterface $bus,
        private RecipientRepository $recipientRepository,
        private DeliveryManager $deliveryManager,
        private AddressRepository $addressRepository
    )
    {
    }

    public function __invoke(CreateDeliveryCommand $event)
    {
        try{

            $devliverModel = $event->deliveryModel;

            $pickupAddress = $this->addressRepository->findMainAddressByCustomer($devliverModel->getCustomer());

            /** @var Recipient|null $recipient */
            $recipient = $this->recipientRepository->findOneBy(['phone' => $devliverModel->getPhone()]);

            if (null === $recipient) {
                $this->logger->info(sprintf("recipient doesn't exist  with phone %s", $devliverModel->getPhone()));

                $r = new Recipient();
            
                $r->setCustomer($devliverModel->getCustomer());
                $r->setFullname($devliverModel->getFullname());
                $r->setPhone($devliverModel->getPhone());
                $r->setCreatedAt(new \DateTimeImmutable('now'));

                $this->em->persist($r);
                $this->em->flush();

                $this->logger->info(sprintf("recipient created with id  %s", $r->getId()));

            } else {
                $this->logger->info(sprintf("recipient  exist  with phone %s", $devliverModel->getPhone()));
            }

            $model = new NewDeliveryModel(
                $devliverModel->getType(),
                $devliverModel->getDescription(), 
                $devliverModel->getDeliveryDate(),
                $recipient ?? $r,
                $devliverModel->getCustomer(),
                $pickupAddress,
                null,
                $devliverModel->getAddress(),
                DeliveryModel::CREATED_FROM_API
            );
    
            $d = $this->deliveryManager->createFrom($model); 

            $this->logger->info(sprintf("delivery created successfully  whith ID %s", $d->getId()));
            
        }catch(\Exception $e){
            $this->logger->info(sprintf('failed treatment delivery model  with ID %s', $devliverModel->getId()));
            $this->logger->error($e->getMessage());
        }
        
    }
}