<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use App\Dto\CreateCustomerDto;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CustomerRepository;
use App\State\CreateCustomerProcessor;
use App\State\DeleteCustomerProcessor;
use ApiPlatform\Metadata\GetCollection;
use App\State\ValidateCustomerProcessor;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PHONE', fields: ['phone'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USER', fields: ['userId'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'customer:get'], 
    operations:[
        new Get(
            security: 'is_granted("ROLE_CUSTOMER_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_CUSTOMER_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_CUSTOMER_CREATE")',
            input: CreateCustomerDto::class,
            processor: CreateCustomerProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_CUSTOMER_UPDATE")',
            denormalizationContext: ['groups' => 'customer:patch'],
            processor: PersistProcessor::class,
        ),
        new Delete(
            security: 'is_granted("ROLE_CUSTOMER_DELETE")',
            processor: DeleteCustomerProcessor::class
        ),
        new Post(
            uriTemplate: '/customers/{id}/activations',
            security: 'is_granted("ROLE_CUSTOMER_ACTIVATE")',
            denormalizationContext: ['groups' => 'customer:activate'],
            processor: ValidateCustomerProcessor::class,
            validationContext: ['groups' => ['activate_customer']]
        )
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'companyName' => 'ipartial',
    'fullname' => 'ipartial',
    'phone' => 'ipartial',
    'phone2' => 'ipartial',
    'email' => 'ipartial',
    'deleted' => 'exact',
    'userId' => 'exact',
    'isVerified' => 'exact',
    'isActivated' => 'exact',
    'docStatus' => 'exact',
    'isPartner' => 'exact'
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]

class Customer implements RessourceInterface
{
    public const string ID_PREFIX = "CU";

    public const string DOC_STATUS_VERIFIED = 'V';
    public const string DOC_STATUS_NOT_VERIFIED = 'N';
    public const string DOC_STATUS_IN_PROGRESS = 'P';
    public const string DOC_STATUS_REFUSED = 'R';

    public const string EVENT_CUSTOMER_CREATED = "created";
    public const string EVENT_CUSTOMER_UPDATED = "updated";
    public const string EVENT_CUSTOMER_DELETED = "deleted";
    public const string EVENT_CUSTOMER_ACTIVATED = "activated";
    public const string EVENT_CUSTOMER_DEACTIVATED = "deactivated";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(groups: ['customer:get', 'delivery:get'])]
    private ?string $id = null;

    #[ORM\Column(length: 60)]
    #[Groups(groups: ['customer:get', 'customer:patch', 'delivery:get'])]
    private ?string $companyName = null;

    #[ORM\Column(length: 120)]
    #[Groups(groups: ['customer:get', 'customer:patch', 'delivery:get'])]
    private ?string $fullname = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(groups: ['customer:get', 'customer:patch'])]
    private ?string $phone = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(groups: ['customer:get', 'customer:patch'])]
    private ?string $phone2 = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Groups(groups: ['customer:get'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(groups: ['customer:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['customer:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(groups: ['customer:get'])]
    private ?bool $deleted = false;

    /**
     * @var Collection<int, Delivery>
     */
    #[ORM\OneToMany(targetEntity: Delivery::class, mappedBy: 'customer')]
    private Collection $deliveries;

    /**
     * @var Collection<int, Address>
     */
    #[ORM\OneToMany(targetEntity: Address::class, mappedBy: 'customer', cascade: ['all'])]
    #[Groups(groups: ['customer:get'])]
    private Collection $addresses;

    /**
     * @var Collection<int, Recipient>
     */
    #[ORM\OneToMany(targetEntity: Recipient::class, mappedBy: 'customer')]
    #[Groups(groups: ['customer:get'])]
    private Collection $recipients;

    /**
     * @var Collection<int, DeliveryModel>
     */
    #[ORM\OneToMany(targetEntity: DeliveryModel::class, mappedBy: 'customer')]
    private Collection $deliveryModels;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['customer:get'])]
    private ?string $userId = null;

    #[ORM\Column]
    #[Groups(groups: ['customer:get'])]
    private ?bool $isVerified = false;

    #[ORM\Column]
    #[Groups(groups: ['customer:get'])]
    private ?bool $isActivated = false;

    #[Assert\Type('bool', groups: ['activate_customer'])]
    #[Assert\NotNull(groups: ['activate_customer'])]
    #[Groups(groups: ['customer:activate'])]
    public bool $activated = false;

    #[ORM\Column(length: 1)]
    #[Groups(groups: ['customer:get'])]
    private ?string $docStatus = self::DOC_STATUS_NOT_VERIFIED;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(groups: ['customer:get'])]
    private ?bool $isPartner = false;

    /**
     * @var Collection<int, Store>
     */
    #[ORM\OneToMany(targetEntity: Store::class, mappedBy: 'customer', cascade: ['all'])]
    #[Groups(groups: ['customer:get'])]
    private Collection $stores;

    public function __construct()
    {
        $this->deliveries = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->recipients = new ArrayCollection();
        $this->deliveryModels = new ArrayCollection();
        $this->stores = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection<int, Delivery>
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function addDelivery(Delivery $delivery): static
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setCustomer($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): static
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getCustomer() === $this) {
                $delivery->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Address>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): static
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setCustomer($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): static
    {
        if ($this->addresses->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getCustomer() === $this) {
                $address->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of phone2
     */ 
    public function getPhone2()
    {
        return $this->phone2;
    }

    /**
     * Set the value of phone2
     *
     * @return  self
     */ 
    public function setPhone2($phone2)
    {
        $this->phone2 = $phone2;

        return $this;
    }

    #[ORM\PreUpdate]
    public function updateUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, Recipient>
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(Recipient $recipient): static
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients->add($recipient);
            $recipient->setCustomer($this);
        }

        return $this;
    }

    public function removeRecipient(Recipient $recipient): static
    {
        if ($this->recipients->removeElement($recipient)) {
            // set the owning side to null (unless already changed)
            if ($recipient->getCustomer() === $this) {
                $recipient->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DeliveryModel>
     */
    public function getDeliveryModels(): Collection
    {
        return $this->deliveryModels;
    }

    public function addDeliveryModel(DeliveryModel $deliveryModel): static
    {
        if (!$this->deliveryModels->contains($deliveryModel)) {
            $this->deliveryModels->add($deliveryModel);
            $deliveryModel->setCustomer($this);
        }

        return $this;
    }

    public function removeDeliveryModel(DeliveryModel $deliveryModel): static
    {
        if ($this->deliveryModels->removeElement($deliveryModel)) {
            // set the owning side to null (unless already changed)
            if ($deliveryModel->getCustomer() === $this) {
                $deliveryModel->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of userId
     */ 
    public function getUserId(): string|null
    {
        return $this->userId;
    }

    /**
     * Set the value of userId
     *
     * @return  self
     */ 
    public function setUserId($userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getIsActivated(): ?bool
    {
        return $this->isActivated;
    }

    public function setIsActivated(bool $isActivated): static
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    /**
     * Get the value of docStatus
     */ 
    public function getDocStatus(): string|null
    {
        return $this->docStatus;
    }

    /**
     * Set the value of docStatus
     *
     * @return  self
     */ 
    public function setDocStatus(?string $docStatus): static
    {
        $this->docStatus = $docStatus;

        return $this;
    }

    public function getIsPartner(): bool|null
    {
        return $this->isPartner;
    }

    /**
     * @return  self
     */ 
    public function setIsPartner(?bool $isPartner): static
    {
        $this->isPartner = $isPartner;

        return $this;
    }

    /**
     * @return Collection<int, Store>
     */
    public function getStores(): Collection
    {
        return $this->stores;
    }

    public function addStore(Store $store): static
    {
        if (!$this->stores->contains($store)) {
            $this->stores->add($store);
            $store->setCustomer($this);
        }

        return $this;
    }

    public function removeStore(Store $store): static
    {
        if ($this->stores->removeElement($store)) {
            // set the owning side to null (unless already changed)
            if ($store->getCustomer() === $this) {
                $store->setCustomer(null);
            }
        }

        return $this;
    }
}
