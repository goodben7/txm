<?php

namespace App\Manager;

use App\Entity\Store;
use App\Model\CreateStoreModel;
use App\Model\UpdateStoreModel;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnavailableDataException;

class StoreManager
{    
    public function __construct(
        private EntityManagerInterface $em, 
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
}