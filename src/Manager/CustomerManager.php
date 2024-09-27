<?php

namespace App\Manager;

use App\Entity\Customer;
use App\Model\NewCustomerModel;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use App\Exception\UnauthorizedActionException;

class CustomerManager
{
    public function __construct(
        private EntityManagerInterface $em, 
    )
    {
    }

    public function createFrom(NewCustomerModel $model): Customer {

        $customer = new Customer();
    
        $customer->setCompanyName($model->companyName);
        $customer->setFullname($model->fullname);
        $customer->setPhone($model->phone);
        $customer->setEmail($model->email);
        $customer->setCreatedAt(new \DateTimeImmutable('now'));

        foreach ($model->addresses as $addr) {
            $customer->addAddress($addr);
        }

        $this->em->persist($customer);
        $this->em->flush();
        
        return $customer;
    }

    private function findCustomer(string $customerId): Customer 
    {
        $customer = $this->em->find(Customer::class, $customerId);

        if (null === $customer) {
            throw new UnavailableDataException(sprintf('cannot find customer with id: %s', $customerId));
        }

        return $customer; 
    }

    public function delete(string $customerId) {
        $customer = $this->findCustomer($customerId);

        if ($customer->isDeleted()) {
            throw new UnauthorizedActionException('this action is not allowed');
        }

        $customer->setDeleted(true);
        $customer->setUpdatedAt(new \DateTimeImmutable('now'));

        $this->em->persist($customer);
        $this->em->flush();
    }

}