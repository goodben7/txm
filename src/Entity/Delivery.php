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
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use App\Dto\ValidateDeliveryDto;
use Doctrine\ORM\Mapping as ORM;
use App\Dto\InprogressDeliveryDto;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\State\DelayDeliveryProcessor;
use App\Repository\DeliveryRepository;
use App\State\CancelDeliveryProcessor;
use App\State\CreateDeliveryProcessor;
use App\State\FinishDeliveryProcessor;
use App\State\PickupDeliveryProcessor;
use ApiPlatform\Metadata\GetCollection;
use App\State\ValidateDeliveryProcessor;
use App\State\InprogressDeliveryProcessor;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

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
            denormalizationContext: ['groups' => 'delivery:patch'],
            processor: PersistProcessor::class,
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
    'terminedBy' => 'exact'
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt', 'deliveryDate', 'validatedAt', 'pickupedAt', 'inprogressAt', 'canceledAt', 'DelayedAt', 'terminedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt', 'deliveryDate', 'validatedAt', 'pickupedAt', 'inprogressAt', 'canceledAt', 'DelayedAt', 'terminedAt'])]

class Delivery
{
    const ID_PREFIX = "DE";

    const TYPE_PACKAGE = "P";
    const TYPE_MAIL = "M";

    const STATUS_PENDING = 'P';
    const STATUS_VALIDATED = 'V';
    const STATUS_PICKUPED = 'U';
    const STATUS_INPROGRESS = 'I';
    const STATUS_DELAYED = 'D';
    const STATUS_TERMINATED = 'T';
    const STATUS_CANCELED = 'C';
    

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

    #[ORM\Column(length: 120)]
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
    #[Groups(groups: ['delivery:get', 'delivery:patch'])]
    private ?Address $pickupAddress = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[Groups(groups: ['delivery:get', 'delivery:patch'])]
    private ?Address $deliveryAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['delivery:get', 'delivery:patch'])]
    private ?string $additionalInformation = null;

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

    public function setTownship(string $township): static
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
}
