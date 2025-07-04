<?php

namespace App\Manager;

use App\Entity\Customer;
use App\Message\Command\CommandBusInterface;
use App\Model\NewCustomerModel;
use App\Model\UserProxyIntertace;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use App\Exception\UnauthorizedActionException;
use App\Message\Command\CreateUserCommand;
use App\Repository\ProfileRepository;

class CustomerManager
{
    public function __construct(
        private EntityManagerInterface $em, 
        private CommandBusInterface $bus,
        private ProfileRepository $profileRepository
    )
    {
    }

    /**
     * Summary of createFrom
     * @param \App\Model\NewCustomerModel $model
     * @throws \App\Exception\UnavailableDataException
     * @return Customer
     */
    public function createFrom(NewCustomerModel $model): Customer {

        $customer = new Customer();
    
        $customer->setCompanyName($model->companyName);
        $customer->setFullname($model->fullname);
        $customer->setPhone($model->phone);
        $customer->setPhone2($model->phone2);
        $customer->setEmail($model->email);
        $customer->setCreatedAt(new \DateTimeImmutable('now'));

        foreach ($model->addresses as $addr) {
            $customer->addAddress($addr);
        }

        try {
            $this->em->persist($customer);
            $this->em->flush();
        } catch (\Exception $e) {
            throw new UnavailableDataException($e->getMessage());
        }

        $profile = $this->profileRepository->findOneBy(['personType' => UserProxyIntertace::PERSON_SENDER]);

        if (null === $profile) {
            throw new UnavailableDataException('cannot find profile with person type: sender');
        }

        $user = $this->bus->dispatch(
            new CreateUserCommand(
                $customer->getEmail(),
                $customer->getEmail(),
                $profile,
                $customer->getPhone(),
                $customer->getFullname(),
                $customer->getId(),
            )
        );

        $customer->setUserId($user->getId());

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