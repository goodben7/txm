<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use App\Dto\DelayDeliveryDto;
use ApiPlatform\Metadata\Post;
use App\Dto\CancelDeliveryDto;
use App\Dto\CreateDeliveryDto;
use App\Dto\FinishDeliveryDto;
use App\Dto\PickupDeliveryDto;
use App\Dto\UpdateDeliveryDto;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use App\Dto\ValidateDeliveryDto;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use App\Dto\InprogressDeliveryDto;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Dto\ReassignationDeliveryDto;
use App\State\DelayDeliveryProcessor;
use App\Repository\DeliveryRepository;
use App\State\CancelDeliveryProcessor;
use App\State\CreateDeliveryProcessor;
use App\State\FinishDeliveryProcessor;
use App\State\PickupDeliveryProcessor;
use App\State\UpdateDeliveryProcessor;
use ApiPlatform\Metadata\GetCollection;
use App\State\ValidateDeliveryProcessor;
use App\State\InprogressDeliveryProcessor;
use App\State\ReassignationDeliveryProcessor;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;


#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'delivery:get'], 
    operations:[
        new Get(
            security: 'is_granted("ROLE_DELIVERY_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_DELIVERY_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_DELIVERY_CREATE")',
            input: CreateDeliveryDto::class,
            processor: CreateDeliveryProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_DELIVERY_UPDATE")',
            input: UpdateDeliveryDto::class,
            processor: UpdateDeliveryProcessor::class,
        ),
        new Post(
            uriTemplate: '/deliveries/cancellations',
            security: 'is_granted("ROLE_DELIVERY_CANCEL")',
            input: CancelDeliveryDto::class,
            processor: CancelDeliveryProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/deliveries/validations',
            security: 'is_granted("ROLE_DELIVERY_VALIDATION")',
            input: ValidateDeliveryDto::class,
            processor: ValidateDeliveryProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/deliveries/pickup',
            security: 'is_granted("ROLE_DELIVERY_PICKUP")',
            input: PickupDeliveryDto::class,
            processor: PickupDeliveryProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/deliveries/inprogress',
            security: 'is_granted("ROLE_DELIVERY_INPROGRESS")',
            input: InprogressDeliveryDto::class,
            processor: InprogressDeliveryProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/deliveries/delay',
            security: 'is_granted("ROLE_DELIVERY_DELAY")',
            input: DelayDeliveryDto::class,
            processor: DelayDeliveryProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/deliveries/finish',
            security: 'is_granted("ROLE_DELIVERY_DELIVER")',
            input: FinishDeliveryDto::class,
            processor: FinishDeliveryProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/deliveries/reassignations',
            security: 'is_granted("ROLE_DELIVERY_REASSIGNATION")',
            input: ReassignationDeliveryDto::class,
            processor: ReassignationDeliveryProcessor::class,
            status: 200
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'status' => 'exact',
    'description' => 'ipartial',
    'type' => 'exact',
    'township' => 'exact',
    'zone' => 'exact',
    'recipient' => 'exact',
    'customer' => 'exact',
    'createdBy' => 'exact',
    'updatedBy' => 'exact',
    'validatedBy' => 'exact',
    'pickupedBy' => 'exact',
    'inprogressBy' => 'exact',
    'canceledBy' => 'exact',
    'DelayedBy' => 'exact',
    'trackingNumber' => 'exact',
    'terminedBy' => 'exact',
    'reassignedBy' => 'exact',
    'createdFrom' => 'exact',
    'createdByTypePerson' => 'exact',
    'deliveryPerson' => 'exact',
    'deliveryPerson.fullname' => 'ipartial',
    'relatedOrder' => 'exact',
    'storeId' => 'exact',
    'recipient.userId' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt', 'deliveryDate', 'validatedAt', 'pickupedAt', 'inprogressAt', 'canceledAt', 'DelayedAt', 'terminedAt', 'reassignedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt', 'deliveryDate', 'validatedAt', 'pickupedAt', 'inprogressAt', 'canceledAt', 'DelayedAt', 'terminedAt', 'reassignedAt'])]

class Delivery implements RessourceInterface
{
    public const string ID_PREFIX = "DE";

    public const string TYPE_PACKAGE = "P";
    public const string TYPE_MAIL = "M";

    public const string STATUS_PENDING = 'P';
    public const string STATUS_VALIDATED = 'V';
    public const string STATUS_PICKUPED = 'U';
    public const string STATUS_INPROGRESS = 'I';
    public const string STATUS_DELAYED = 'D';
    public const string STATUS_TERMINATED = 'T';
    public const string STATUS_CANCELED = 'C';

    public const string CREATED_FROM_WEB_APP = "WEB_APP";
    public const string CREATED_FROM_MOBILE_APP = "MOBILE_APP";
    public const string CREATED_FROM_API = "API";

    public const string EVENT_DELIVERY_CREATED = "created";
    public const string EVENT_DELIVERY_UPDATED = "updated";
    public const string EVENT_DELIVERY_CANCELED = "canceled";
    public const string EVENT_DELIVERY_PICKUPED = "pickuped";
    public const string EVENT_DELIVERY_INPROGRESS = "inprogress";
    public const string EVENT_DELIVERY_DELAYED = "delayed";
    public const string EVENT_DELIVERY_TERMINATED = "terminated";
    public const string EVENT_DELIVERY_VALIDATED = "validated";
    public const string EVENT_DELIVERY_REASSIGNED = "reassigned";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $id = null;

    #[ORM\Column]
    #[Groups(groups: ['delivery:get'])]
    private ?\DateTimeImmutable $deliveryDate = null;

    #[ORM\Column(length: 1)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $message = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(groups: ['delivery:get', 'delivery:patch'])]
    private ?string $description = null;

    #[ORM\Column(length: 1)]
    #[Groups(groups: ['delivery:get', 'delivery:patch'])]
    private ?string $type = null;

    #[ORM\Column(length: 120, nullable: true)]
    #[Groups(groups: ['delivery:get', 'delivery:patch'])]
    private ?string $township = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['delivery:get', 'delivery:patch'])]
    private ?Recipient $recipient = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['delivery:get', 'delivery:patch'])]
    private ?Customer $customer = null;

    #[ORM\Column(length: 16)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $createdBy = null;

    #[ORM\Column]
    #[Groups(groups: ['delivery:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $updatedBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $validatedBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $pickupedBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?\DateTimeImmutable $pickupedAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $inprogressBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?\DateTimeImmutable $inprogressAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $canceledBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?\DateTimeImmutable $canceledAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $DelayedBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?\DateTimeImmutable $DelayedAt = null;

    #[ORM\Column(length: 16)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $trackingNumber = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $terminedBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?\DateTimeImmutable $terminedAt = null;

    #[ORM\Column(length: 120, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $zone = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['delivery:get', 'delivery:patch'])]
    private ?Address $pickupAddress = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['delivery:get', 'delivery:patch'])]
    private ?Address $deliveryAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['delivery:get', 'delivery:patch'])]
    private ?string $additionalInformation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $createdFrom = null;

    #[ORM\Column(length: 5, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $createdByTypePerson = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?DeliveryPerson $deliveryPerson = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $reassignedBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?\DateTimeImmutable $reassignedAt = null;

    #[ORM\OneToOne(mappedBy: 'delivery', cascade: ['persist', 'remove'])]
    #[Groups(groups: ['delivery:get'])]
    private ?Order $relatedOrder = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['delivery:get'])]
    private ?string $storeId = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(\DateTimeImmutable $deliveryDate): static
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTownship(): ?string
    {
        return $this->township;
    }

    public function setTownship(?string $township): static
    {
        $this->township = $township;

        return $this;
    }

    public function getRecipient(): ?Recipient
    {
        return $this->recipient;
    }

    public function setRecipient(?Recipient $recipient): static
    {
        $this->recipient = $recipient;

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

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(string $createdBy): static
    {
        $this->createdBy = $createdBy;

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

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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

    public function getPickupedBy(): ?string
    {
        return $this->pickupedBy;
    }

    public function setPickupedBy(?string $pickupedBy): static
    {
        $this->pickupedBy = $pickupedBy;

        return $this;
    }

    public function getPickupedAt(): ?\DateTimeImmutable
    {
        return $this->pickupedAt;
    }

    public function setPickupedAt(?\DateTimeImmutable $pickupedAt): static
    {
        $this->pickupedAt = $pickupedAt;

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

    public function getCanceledBy(): ?string
    {
        return $this->canceledBy;
    }

    public function setCanceledBy(?string $canceledBy): static
    {
        $this->canceledBy = $canceledBy;

        return $this;
    }

    public function getCanceledAt(): ?\DateTimeImmutable
    {
        return $this->canceledAt;
    }

    public function setCanceledAt(?\DateTimeImmutable $canceledAt): static
    {
        $this->canceledAt = $canceledAt;

        return $this;
    }

    public function getDelayedBy(): ?string
    {
        return $this->DelayedBy;
    }

    public function setDelayedBy(?string $DelayedBy): static
    {
        $this->DelayedBy = $DelayedBy;

        return $this;
    }

    public function getDelayedAt(): ?\DateTimeImmutable
    {
        return $this->DelayedAt;
    }

    public function setDelayedAt(?\DateTimeImmutable $DelayedAt): static
    {
        $this->DelayedAt = $DelayedAt;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(string $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    #[ORM\PreUpdate]
    public function updateUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getTerminedAt(): ?\DateTimeImmutable
    {
        return $this->terminedAt;
    }

    public function setTerminedAt(?\DateTimeImmutable $terminedAt): static
    {
        $this->terminedAt = $terminedAt;

        return $this;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(?string $zone): static
    {
        $this->zone = $zone;

        return $this;
    }

    public function getPickupAddress(): ?Address
    {
        return $this->pickupAddress;
    }

    public function setPickupAddress(?Address $pickupAddress): static
    {
        $this->pickupAddress = $pickupAddress;

        return $this;
    }

    public function getDeliveryAddress(): ?Address
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?Address $deliveryAddress): static
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getAdditionalInformation(): ?string
    {
        return $this->additionalInformation;
    }

    public function setAdditionalInformation(?string $additionalInformation): static
    {
        $this->additionalInformation = $additionalInformation;

        return $this;
    }

    public function getCreatedFrom(): ?string
    {
        return $this->createdFrom;
    }

    public function setCreatedFrom(?string $createdFrom): static
    {
        $this->createdFrom = $createdFrom;

        return $this;
    }

    public function getCreatedByTypePerson(): ?string
    {
        return $this->createdByTypePerson;
    }

    public function setCreatedByTypePerson(?string $createdByTypePerson): static
    {
        $this->createdByTypePerson = $createdByTypePerson;

        return $this;
    }

    public function getDeliveryPerson(): ?DeliveryPerson
    {
        return $this->deliveryPerson;
    }

    public function setDeliveryPerson(?DeliveryPerson $deliveryPerson): static
    {
        $this->deliveryPerson = $deliveryPerson;

        return $this;
    }

    /**
     * Get the value of reassignedBy
     */ 
    public function getReassignedBy(): string|null
    {
        return $this->reassignedBy;
    }

    /**
     * Set the value of reassignedBy
     *
     * @return  self
     */ 
    public function setReassignedBy(?string $reassignedBy)
    {
        $this->reassignedBy = $reassignedBy;

        return $this;
    }

    /**
     * Get the value of reassignedAt
     */ 
    public function getReassignedAt(): \DateTimeImmutable|null
    {
        return $this->reassignedAt;
    }

    /**
     * Set the value of reassignedAt
     *
     * @return  self
     */ 
    public function setReassignedAt(?\DateTimeImmutable $reassignedAt)
    {
        $this->reassignedAt = $reassignedAt;

        return $this;
    }

    public function getRelatedOrder(): ?Order
    {
        return $this->relatedOrder;
    }

    public function setRelatedOrder(?Order $relatedOrder): static
    {
        // unset the owning side of the relation if necessary
        if ($relatedOrder === null && $this->relatedOrder !== null) {
            $this->relatedOrder->setDelivery(null);
        }

        // set the owning side of the relation if necessary
        if ($relatedOrder !== null && $relatedOrder->getDelivery() !== $this) {
            $relatedOrder->setDelivery($this);
        }

        $this->relatedOrder = $relatedOrder;

        return $this;
    }

    /**
     * Get the value of storeId
     */ 
    public function getStoreId(): string|null
    {
        return $this->storeId;
    }

    /**
     * Set the value of storeId
     *
     * @return  self
     */ 
    public function setStoreId(?string $storeId): static
    {
        $this->storeId = $storeId;

        return $this;
    }
}
