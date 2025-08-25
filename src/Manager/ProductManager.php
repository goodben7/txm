<?php

namespace App\Manager;

use App\Entity\Product;
use App\Model\CreateProductModel;
use App\Service\ActivityEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\UnauthorizedActionException;
use App\Exception\UnavailableDataException;

class ProductManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private ActivityEventDispatcher $eventDispatcher,
    )
    {
    }

    public function createFrom(CreateProductModel $model): Product
    {
        $product = new Product();

        $product->setName($model->name);
        $product->setDescription($model->description);
        $product->setPrice($model->price);
        $product->setStore($model->store);
        $product->setActive($model->active);
        $product->setType($model->type);
        $product->setIsVerified($model->isVerified);
        $product->setCreatedAt(new \DateTimeImmutable());
       
        $this->em->persist($product);
        $this->em->flush();
        
        return $product;
    }
    
    private function findProduct(string $productId): Product 
    {
        $product = $this->em->find(Product::class, $productId);

        if (null === $product) {
            throw new UnavailableDataException(sprintf('cannot find product with id: %s', $productId));
        }

        return $product; 
    }
    
    public function validate(string $productId, bool $isVerified = true): Product
    {
        $product = $this->findProduct($productId);

        if ($product->getIsVerified() === $isVerified) {
            throw new UnauthorizedActionException('this action is not allowed');
        }

        $product->setIsVerified($isVerified);
        $product->setUpdatedAt(new \DateTimeImmutable('now'));

        $this->em->persist($product);
        $this->em->flush();

        if ($isVerified) {
            $this->eventDispatcher->dispatch(
                $product,
                Product::EVENT_PRODUCT_VALIDATED,
                null,
                null
            );
        }

        return $product;
    }
}