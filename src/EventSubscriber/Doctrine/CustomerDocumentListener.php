<?php
namespace App\EventSubscriber\Doctrine;

use App\Entity\Customer;
use App\Entity\Document;
use Doctrine\ORM\Events;
use App\Repository\CustomerRepository;
use App\Repository\DocumentRepository;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
class CustomerDocumentListener  {

    public function __construct(
        private EntityManagerInterface $em,
        private DocumentRepository $documentRepository,
        private CustomerRepository $customerRepository,
        private ActivityEventDispatcher $eventDispatcher,
    )
    {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        /** @var Document $document */
        $document = $args->getObject();

        if (!$document instanceof Document) {
            return;
        }

        $this->handleCustomerKYCStatus($document->getHolderId());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        /** @var Document $document */
        $document = $args->getObject();

        if (!$document instanceof Document) {
            return;
        }

        $this->handleCustomerKYCStatus($document->getHolderId());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        /** @var Document $document */
        $document = $args->getObject();

        if (!$document instanceof Document) {
            return;
        }

        $this->handleCustomerKYCStatus($document->getHolderId());
    }

    private function handleCustomerKYCStatus(string $customerId): void {

        /** @var Customer $customer */
        $customer = $this->customerRepository->find($customerId);
        
        // If customer not found, exit early
        if (!$customer) {
            return;
        }

        $documents = $this->documentRepository->findAllByCustomerHolderId($customerId);


        $verified = 0;
        $pending = 0;
        $refused = 0;
        $count = 0;

        foreach ($documents as $doc) {
            if (Document::STATUS_PENDING === $doc->getStatus()) {
                $pending++;
            }

            if (Document::STATUS_REFUSED === $doc->getStatus()) {
                $refused++;
            }

            if (Document::STATUS_VALIDATED === $doc->getStatus()) {
                $verified++;
            }

            $count++;
        }

        if ($count === 0) {
            $customer->setDocStatus(Customer::DOC_STATUS_NOT_VERIFIED);
            $customer->setIsActivated(false);
            $customer->setIsVerified(false);
        }
        elseif ($count === $verified) {
            $customer->setDocStatus(Customer::DOC_STATUS_VERIFIED);
            $customer->setIsActivated(true);
            $customer->setIsVerified(true);
            $this->eventDispatcher->dispatch(
                $customer, 
                Customer::EVENT_CUSTOMER_ACTIVATED, 
                null, 
                null)
            ;
        }
        elseif ($count === $pending) {
            $customer->setDocStatus(Customer::DOC_STATUS_NOT_VERIFIED);
            $customer->setIsActivated(false);
            $customer->setIsVerified(false);
        }
        elseif ($count === $refused) {
            $customer->setDocStatus(Customer::DOC_STATUS_REFUSED);
            $customer->setIsActivated(false);
            $customer->setIsVerified(false);
        }
        else {
            $customer->setDocStatus(Customer::DOC_STATUS_IN_PROGRESS);
            $customer->setIsActivated(false);
            $customer->setIsVerified(false);
        }

        $this->em->persist($customer);
        $this->em->flush();
    }
}