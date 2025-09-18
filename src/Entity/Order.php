<?php

namespace App\Entity;

use App\Dto\CreateOrderDto;
use App\Dto\FinishOrderDto;
use App\Dto\RejectOrderDto;
use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use App\Dto\ValidateOrderDto;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use App\Dto\InprogressOrderDto;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use App\State\RejectOrdeProcessor;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\OrderRepository;
use App\State\CreateOrderProcessor;
use App\State\FinishOrderProcessor;
use ApiPlatform\Metadata\ApiResource;
use App\State\ValidateOrderProcessor;
use ApiPlatform\Metadata\GetCollection;
use App\State\InprogressOrderProcessor;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'order:get'], 
    operations:[
        new Get(
            security: 'is_granted("ROLE_ORDER_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_ORDER_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_ORDER_CREATE")',
            input: CreateOrderDto::class,
            processor: CreateOrderProcessor::class,
        ),
        new Post(
            uriTemplate: '/orders/validations',
            security: 'is_granted("ROLE_ORDER_VALIDATION")',
            input: ValidateOrderDto::class,
            processor: ValidateOrderProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/orders/rejections',
            security: 'is_granted("ROLE_ORDER_REJECT")',
            input: RejectOrderDto::class,
            processor: RejectOrdeProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/orders/inprogress',
            security: 'is_granted("ROLE_ORDER_INPROGRESS")',
            input: InprogressOrderDto::class,
            processor: InprogressOrderProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/orders/finish',
            security: 'is_granted("ROLE_ORDER_FINISH")',
            input: FinishOrderDto::class,
            processor: FinishOrderProcessor::class,
            status: 200
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'status' => 'exact',
    'createdBy' => 'exact',
    'validatedBy' => 'exact',
    'rejectedBy' => 'exact',
    'inprogressBy' => 'exact',
    'store' => 'exact',
    'customer' => 'exact',
    'userId' => 'exact',
    'delivery' => 'exact',
    'isFromMerchant' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'validatedAt', 'rejectedAt', 'inprogressAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'validatedAt', 'rejectedAt', 'inprogressAt'])]
class Order implements RessourceInterface
{
    public const string ID_PREFIX = "OR";

    public const string STATUS_PENDING = 'P';
    public const string STATUS_VALIDATED = 'V';
    public const string STATUS_DELIVERED  = 'D';
    public const string STATUS_IN_PROGRESS = 'I';
    public const string STATUS_COMPLETED = 'C';
    public const string STATUS_REJECTED = 'R';
    public const string STATUS_IN_DELIVERY = 'L';

    public const string EVENT_ORDER_VALIDATED = "order_validated";
    public const string EVENT_ORDER_CREATED = "order_created";
    public const string EVENT_ORDER_REJECTED = "order_rejected";
    public const string EVENT_ORDER_INPROGRESS = "order_inprogress";
    public const string EVENT_ORDER_TERMINATED = "order_terminated";
    public const string EVENT_ORDER_IN_DELIVERY = "order_in_delivery";
    public const string EVENT_ORDER_DELIVERED = "order_delivered";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(groups: ['order:get'])]
    private ?string $id = null;

    #[ORM\Column(length: 1)]
    #[Groups(groups: ['order:get'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(groups: ['order:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?string $createdBy = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 17, scale: 2)]
    #[Groups(groups: ['order:get'])]
    private ?string $totalPrice = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?string $validatedBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?\DateTimeImmutable $rejectedAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?string $rejectedBy = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?string $inprogressBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?\DateTimeImmutable $inprogressAt = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'orderReference',  cascade: ['all'])]
    #[Groups(groups: ['order:get'])]
    private Collection $orderItems;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['order:get'])]
    private ?Customer $customer = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?Store $store = null;

    #[ORM\Column(length: 16, nullable:true)]
    #[Groups(groups: ['order:get'])]
    private ?string $userId = null;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?Address $pickupAddress = null;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?Address $deliveryAddress = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?string $description = null;

    #[ORM\OneToOne(inversedBy: 'relatedOrder', cascade: ['persist', 'remove'])]
    #[Groups(groups: ['order:get'])]
    private ?Delivery $delivery = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?string $terminedBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['order:get'])]
    private ?\DateTimeImmutable $terminedAt = null;

    #[ORM\Column(type: "integer", nullable:true, options: ["unsigned" => true])]
    #[Groups(groups: ['order:get'])]
    private ?int $serialNumber = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ["default" => false])]
    #[Groups(groups: ['order:get'])]
    private bool $isFromMerchant = false;
    
    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTotalPrice(): ?string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(string $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrderReference($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrderReference() === $this) {
                $orderItem->setOrderReference(null);
            }
        }

        return $this;
    }

    public function getValidatedBy(): ?string
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(?string $validatedBy): static
    {
        $this->validatedBy = $validatedBy;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeImmutable $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    /**
     * Get the value of createdBy
     */ 
    public function getCreatedBy(): string|null
    {
        return $this->createdBy;
    }

    /**
     * Set the value of createdBy
     *
     * @return  self
     */ 
    public function setCreatedBy(?string $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getRejectedAt(): ?\DateTimeImmutable
    {
        return $this->rejectedAt;
    }

    public function setRejectedAt(?\DateTimeImmutable $rejectedAt): static
    {
        $this->rejectedAt = $rejectedAt;

        return $this;
    }

    public function getRejectedBy(): ?string
    {
        return $this->rejectedBy;
    }

    public function setRejectedBy(?string $rejectedBy): static
    {
        $this->rejectedBy = $rejectedBy;

        return $this;
    }

    public function getInprogressBy(): ?string
    {
        return $this->inprogressBy;
    }

    public function setInprogressBy(?string $inprogressBy): static
    {
        $this->inprogressBy = $inprogressBy;

        return $this;
    }

    public function getInprogressAt(): ?\DateTimeImmutable
    {
        return $this->inprogressAt;
    }

    public function setInprogressAt(?\DateTimeImmutable $inprogressAt): static
    {
        $this->inprogressAt = $inprogressAt;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): static
    {
        $this->store = $store;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the value of pickupAddress
     */ 
    public function getPickupAddress(): Address|null
    {
        return $this->pickupAddress;
    }

    /**
     * Set the value of pickupAddress
     *
     * @return  self
     */ 
    public function setPickupAddress(?Address $pickupAddress): static
    {
        $this->pickupAddress = $pickupAddress;

        return $this;
    }

    /**
     * Get the value of deliveryAddress
     */ 
    public function getDeliveryAddress(): Address|null
    {
        return $this->deliveryAddress;
    }

    /**
     * Set the value of deliveryAddress
     *
     * @return  self
     */ 
    public function setDeliveryAddress(?Address $deliveryAddress): static
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(?Delivery $delivery): static
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getTerminedBy(): ?string
    {
        return $this->terminedBy;
    }

    public function setTerminedBy(?string $terminedBy): static
    {
        $this->terminedBy = $terminedBy;

        return $this;
    }
    
    #[ORM\PrePersist]
    public function generateSerialNumber(\Doctrine\ORM\Event\PrePersistEventArgs $args): void
    {
        if ($this->serialNumber === null) {
           
            $entityManager = $args->getObjectManager();

            /** @var \App\Repository\OrderRepository $repository */
            $repository = $entityManager->getRepository(Order::class);
            $result = $repository->createQueryBuilder('o')
                ->select('o.serialNumber')
                ->orderBy('o.serialNumber', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
            
            $lastSerialNumber = $result ? $result['serialNumber'] : null;
            $this->serialNumber = $lastSerialNumber ? ($lastSerialNumber + 1) : 1;
        }
    }

    public function getTerminedAt(): ?\DateTimeImmutable
    {
        return $this->terminedAt;
    }

    public function setTerminedAt(?\DateTimeImmutable $terminedAt): static
    {
        $this->terminedAt = $terminedAt;

        return $this;
    }

    /**
     * Get the value of serialNumber
     * @return string|null Returns the serial number formatted with leading zeros (e.g. 00001)
     */ 
    public function getSerialNumber(): string|null
    {
        return $this->serialNumber !== null ? sprintf('%05d', $this->serialNumber) : null;
    }

    /**
     * Set the value of serialNumber
     *
     * @return  self
     */ 
    public function setSerialNumber(?int $serialNumber): static
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * Get the value of isFromMerchant
     */ 
    public function getIsFromMerchant(): bool
    {
        return $this->isFromMerchant;
    }

    /**
     * Set the value of isFromMerchant
     *
     * @return  self
     */ 
    public function setIsFromMerchant(?bool $isFromMerchant): static
    {
        $this->isFromMerchant = $isFromMerchant;

        return $this;
    }
}
