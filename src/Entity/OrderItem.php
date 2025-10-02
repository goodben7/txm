<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderItemRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['order:get'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderReference = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['order:get'])]
    private ?Product $product = null;

    #[ORM\Column]
    #[Groups(groups: ['order:get'])]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 17, scale: 2)]
    #[Groups(groups: ['order:get'])]
    private ?string $unitPrice = null;
    
    /**
     * @var Collection<int, OrderItemOption>
     */
    #[ORM\OneToMany(targetEntity: OrderItemOption::class, mappedBy: 'orderItem', cascade: ['persist', 'remove'])]
    #[Groups(groups: ['order:get'])]
    private Collection $orderItemOptions;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderReference(): ?Order
    {
        return $this->orderReference;
    }

    public function setOrderReference(?Order $orderReference): static
    {
        $this->orderReference = $orderReference;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function __construct()
    {
        $this->orderItemOptions = new ArrayCollection();
    }

    /**
     * @return Collection<int, OrderItemOption>
     */
    public function getOrderItemOptions(): Collection
    {
        return $this->orderItemOptions;
    }

    public function addOrderItemOption(OrderItemOption $orderItemOption): static
    {
        if (!$this->orderItemOptions->contains($orderItemOption)) {
            $this->orderItemOptions->add($orderItemOption);
            $orderItemOption->setOrderItem($this);
        }

        return $this;
    }

    public function removeOrderItemOption(OrderItemOption $orderItemOption): static
    {
        if ($this->orderItemOptions->removeElement($orderItemOption)) {
            // set the owning side to null (unless already changed)
            if ($orderItemOption->getOrderItem() === $this) {
                $orderItemOption->setOrderItem(null);
            }
        }

        return $this;
    }
}
