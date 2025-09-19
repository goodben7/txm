<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class OrderEstimateResponse
{
    #[Groups(groups: ['order:get'])]
    private ?string $deliveryTax = null;
    #[Groups(groups: ['order:get'])]
    private ?string $deliveryFee = null;
    #[Groups(groups: ['order:get'])]
    private ?string $totalPrice = null;
    #[Groups(groups: ['order:get'])]
    private ?string $subtotal = null;

    /**
     * Create an OrderEstimateResponse from an Order entity
     * 
     * @param \App\Entity\Order $order The order to extract data from
     * @return self
     */
    public static function fromOrder(\App\Entity\Order $order): self
    {
        $response = new self();
        $response->setDeliveryTax($order->getDeliveryTax());
        $response->setDeliveryFee($order->getDeliveryFee());
        $response->setTotalPrice($order->getTotalPrice());
        $response->setSubtotal($order->getSubtotal());
        
        return $response;
    }

    /**
     * Get the delivery tax
     */ 
    public function getDeliveryTax(): ?string
    {
        return $this->deliveryTax;
    }

    /**
     * Set the delivery tax
     *
     * @return  self
     */ 
    public function setDeliveryTax(?string $deliveryTax): self
    {
        $this->deliveryTax = $deliveryTax;

        return $this;
    }

    /**
     * Get the delivery fee
     */ 
    public function getDeliveryFee(): ?string
    {
        return $this->deliveryFee;
    }

    /**
     * Set the delivery fee
     *
     * @return  self
     */ 
    public function setDeliveryFee(?string $deliveryFee): self
    {
        $this->deliveryFee = $deliveryFee;

        return $this;
    }

    /**
     * Get the total price
     */ 
    public function getTotalPrice(): ?string
    {
        return $this->totalPrice;
    }

    /**
     * Set the total price
     *
     * @return  self
     */ 
    public function setTotalPrice(?string $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get the subtotal
     */ 
    public function getSubtotal(): ?string
    {
        return $this->subtotal;
    }

    /**
     * Set the subtotal
     *
     * @return  self
     */ 
    public function setSubtotal(?string $subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }
}