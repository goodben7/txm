<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderItemOptionRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderItemOptionRepository::class)]
class OrderItemOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['order:get'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderItemOptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?OrderItem $orderItem = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['order:get'])]
    private ?ProductOptionValue $optionValue = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderItem(): ?OrderItem
    {
        return $this->orderItem;
    }

    public function setOrderItem(?OrderItem $orderItem): static
    {
        $this->orderItem = $orderItem;

        return $this;
    }

    public function getOptionValue(): ?ProductOptionValue
    {
        return $this->optionValue;
    }

    public function setOptionValue(?ProductOptionValue $optionValue): static
    {
        $this->optionValue = $optionValue;

        return $this;
    }
}