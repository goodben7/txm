<?php

namespace App\Manager;

use App\Entity\Customer;
use App\Entity\DeliveryModel;
use App\Model\CreateDeliveryModel;
use App\Service\EncryptionService;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use Symfony\Component\HttpFoundation\RequestStack;

class DeliveryModelManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private CustomerRepository $customerRepository,
        private EncryptionService $encryptionService
    )
    {
    }

    public function createFrom(CreateDeliveryModel $model): DeliveryModel {

        
        $d = new DeliveryModel();

        $request = $this->requestStack->getCurrentRequest();
        $apikey = $request->headers->get('apikey');

        if (null === $apikey) {
            throw new UnavailableDataException('api key does not exist');
        }

        $customerId = $this->encryptionService->decrypt($apikey);

        /** @var Customer|null $customer */
        $customer = $this->customerRepository->findOneBy(['id' => $customerId]);

        if (null === $customer) {
            throw new UnavailableDataException('cannot find api key');
        }


        $d->setFullname($model->fullname);
        $d->setPhone($model->phone);
        $d->setType($model->type);
        $d->setDescription($model->description);
        $d->setDeliveryDate($model->deliveryDate);
        $d->setAddress($model->address);
        $d->setAmount($model->amount);
        $d->setNumberMP($model->numberMP);
        $d->setCreatedAt(new \DateTimeImmutable('now'));
        $d->setCreatedBy($customer->getId());
        $d->setCustomer($customer);
        $d->setApikey($customerId);
        $d->setData1($model->data1);
        $d->setData2($model->data2);
        $d->setData3($model->data3);
        $d->setData4($model->data4);
        
        $this->em->persist($d);
        $this->em->flush();
        
        return $d;
    }
}