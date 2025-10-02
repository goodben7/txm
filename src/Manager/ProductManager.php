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
        $product->setCurrency($model->currency);
        $product->setCreatedAt(new \DateTimeImmutable());
        
        // Persist the product first to get an ID
        $this->em->persist($product);
        
        // Process product options if provided
        if (!empty($model->productOptions)) {
            foreach ($model->productOptions as $optionData) {
                if (isset($optionData['name'])) {
                    // Create new product option
                    $option = new \App\Entity\ProductOption();
                    $option->setName($optionData['name']);
                    $option->setProduct($product);
                    
                    $this->em->persist($option);
                    
                    // Process option values if provided
                    if (isset($optionData['values']) && is_array($optionData['values'])) {
                        foreach ($optionData['values'] as $valueData) {
                            if (isset($valueData['value']) && isset($valueData['priceAdjustment'])) {
                                // Create new option value
                                $optionValue = new \App\Entity\ProductOptionValue();
                                $optionValue->setValue($valueData['value']);
                                $optionValue->setPriceAdjustment($valueData['priceAdjustment']);
                                $optionValue->setOptions($option);
                                
                                $this->em->persist($optionValue);
                            }
                        }
                    }
                }
            }
        }
        
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