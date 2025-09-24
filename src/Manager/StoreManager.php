<?php

namespace App\Manager;

use App\Entity\Store;
use App\Model\CreateStoreModel;
use App\Model\UpdateStoreModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;
use App\Exception\UnauthorizedActionException;

class StoreManager
{    
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
    )
    {
    }

    public function createFrom(CreateStoreModel $model): Store 
    {
        $store = new Store();

        $store->setLabel($model->label);
        $store->setDescription($model->description);
        $store->setEmail($model->email);
        $store->setPhone($model->phone);
        $store->setActive($model->active);
        $store->setCreatedAt(new \DateTimeImmutable());
        $store->setCustomer($model->customer);
        $store->setService($model->service);
        $store->setAddress($model->address);

        $this->em->persist($store);
        $this->em->flush();

        return $store;  
    }

    private function findStore(string $storeId): Store 
    {
        $store = $this->em->find(Store::class, $storeId);

        if (null === $store) {
            throw new UnavailableDataException(sprintf('cannot find store with id: %s', $storeId));
        }

        return $store; 
    }

    public function updateFrom(UpdateStoreModel $model, string $storeId): Store 
    {
        $store = $this->findStore($storeId);

        $store->setLabel($model->label);
        $store->setDescription($model->description);
        $store->setEmail($model->email);
        $store->setPhone($model->phone);
        $store->setActive($model->active);
        $store->setUpdatedAt(new \DateTimeImmutable());
        $store->setService($model->service);

        $this->em->persist($store);
        $this->em->flush();

        return $store;
    }

    public function validate(string $storeId, bool $isVerified = true): Store
    {
        $store = $this->findStore($storeId);

        if ($store->getIsVerified() === $isVerified) {
            throw new UnauthorizedActionException('this action is not allowed');
        }

        $store->setIsVerified($isVerified);
        $store->setUpdatedAt(new \DateTimeImmutable('now'));

        $customer = $store->getCustomer();
        $customer->setIsPartner(true);

        $this->em->persist($store);
        $this->em->persist($customer);
        
        $this->em->flush();

        if ($isVerified) {
            $this->eventDispatcher->dispatch(
                $store,
                Store::EVENT_STORE_VERIFIED,
                null,
                null
            );
        }

        return $store;
    }
}