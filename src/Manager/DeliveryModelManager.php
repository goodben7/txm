<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\Customer;
use App\Entity\DeliveryModel;
use App\Model\CreateDeliveryModel;
use App\Message\Query\GetUserDetails;
use App\Repository\CustomerRepository;
use App\Message\Query\QueryBusInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class DeliveryModelManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private QueryBusInterface $queries,
        private RequestStack $requestStack,
        private CustomerRepository $customerRepository
    )
    {
    }

    public function createFrom(CreateDeliveryModel $model): DeliveryModel {

        $userId = $this->security->getUser()->getUserIdentifier();

        /** @var User $user */
        $user = $this->queries->ask(new GetUserDetails($userId));
        
        $d = new DeliveryModel();

        $request = $this->requestStack->getCurrentRequest();
        $customerId = $request->headers->get('apikey');

        if (null === $customerId) {
            throw new UnavailableDataException('api key does not exist');
        }

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
        $d->setCreatedBy($user->getId());
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