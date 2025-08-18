<?php

namespace App\Manager;

use App\Entity\Product;
use App\Model\CreateProductModel;
use Doctrine\ORM\EntityManagerInterface;

class ProductManager
{
    public function __construct(
        private EntityManagerInterface $em,
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
        $product->setCreatedAt(new \DateTimeImmutable());
       
        $this->em->persist($product);
        $this->em->flush();
        
        return $product;
    } 
}