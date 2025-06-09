<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use App\Dto\CreateRecipientDto;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\RecipientRepository;
use App\State\CreateRecipientProcessor;
use App\State\DeleteRecipientProcessor;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: RecipientRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PHONE', fields: ['phone'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'recipient:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_RECIPIENT_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_RECIPIENT_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_RECIPIENT_CREATE")',
            input: CreateRecipientDto::class,
            processor: CreateRecipientProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_RECIPIENT_UPDATE")',
            denormalizationContext: ['groups' => 'recipient:patch'],
            processor: PersistProcessor::class,
        ),
        new Delete(
            security: 'is_granted("ROLE_RECIPIENT_DELETE")',
            processor: DeleteRecipientProcessor::class
        )
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'fullname' => 'ipartial',
    'phone' => 'ipartial',
    'phone2' => 'ipartial',
    'email' => 'ipartial',
    'deleted' => 'exact',
    'customer' => 'exact',
    'recipientType' => 'exact',

])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class Recipient implements RessourceInterface
{
    public const string ID_PREFIX = "RE";

    public const string EVENT_USER_RECIPIENT = "registrated";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(groups: ['recipient:get'])]
    private ?string $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(groups: ['recipient:get', 'recipient:patch', 'delivery:get'])]
    private ?string $fullname = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(groups: ['recipient:get', 'recipient:patch'])]
    private ?string $phone = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(groups: ['recipient:get', 'recipient:patch'])]
    private ?string $phone2 = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    #[Groups(groups: ['recipient:get', 'recipient:patch'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(groups: ['recipient:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['recipient:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(groups: ['recipient:get'])]
    private ?bool $deleted = false;

    /**
     * @var Collection<int, Delivery>
     */
    #[ORM\OneToMany(targetEntity: Delivery::class, mappedBy: 'recipient')]
    private Collection $deliveries;

    #[ORM\ManyToOne(inversedBy: 'recipients')]
    #[Groups(groups: ['recipient:get'])]
    private ?Customer $customer = null;

    /**
     * @var Collection<int, Address>
     */
    #[ORM\OneToMany(targetEntity: Address::class, mappedBy: 'recipient', cascade: ['all'])]
    #[Groups(groups: ['recipient:get'])]
    private Collection $addresses;

    #[ORM\ManyToOne]
    #[Groups(groups: ['recipient:get', 'recipient:patch'])]
    private ?RecipientType $recipientType = null;

    public function __construct()
    {
        $this->deliveries = new ArrayCollection();
        $this->addresses = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
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

    #[ORM\PreUpdate]
    public function updateUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function buildCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
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
            $delivery->setRecipient($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): static
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getRecipient() === $this) {
                $delivery->setRecipient(null);
            }
        }

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
            $address->setRecipient($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): static
    {
        if ($this->addresses->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getRecipient() === $this) {
                $address->setRecipient(null);
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

    public function getRecipientType(): ?RecipientType
    {
        return $this->recipientType;
    }

    public function setRecipientType(?RecipientType $recipientType): static
    {
        $this->recipientType = $recipientType;

        return $this;
    }
}
