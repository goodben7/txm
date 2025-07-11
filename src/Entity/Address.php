<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\AddressRepository;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => 'address:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_ADDRESS_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_ADDRESS_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_ADDRESS_CREATE")',
            denormalizationContext: ['groups' => 'address:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_ADDRESS_UPDATE")',
            denormalizationContext: ['groups' => 'address:patch'],
            processor: PersistProcessor::class,
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'label' => 'ipartial',
    'address' => 'ipartial',
    'isMain' => 'exact',
    'isPublic' => 'exact',
    'customer' => 'exact',
    'recipient' => 'exact'
])]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['customer:get', 'address:get', 'recipient:get'])]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    #[Groups(groups: ['customer:get', 'address:get', 'address:post', 'address:patch', 'recipient:get'])]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(groups: ['customer:get', 'address:get', 'address:post', 'address:patch', 'recipient:get'])]
    private ?string $address = null;

    #[ORM\Column]
    #[Groups(groups: ['customer:get', 'address:get', 'address:post', 'address:patch', 'recipient:get'])]
    private ?bool $isMain = false;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['address:post'])]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    #[Groups(groups: ['address:post'])]
    private ?Recipient $recipient = null;

    /**
     * @var Collection<int, Delivery>
     */
    #[ORM\OneToMany(targetEntity: Delivery::class, mappedBy: 'pickupAddress')]
    private Collection $deliveries;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    #[Groups(groups: ['address:get', 'address:post', 'address:patch'])]
    private ?Township $township = null;

    #[ORM\Column]
    #[Groups(groups: ['customer:get', 'address:get', 'address:post', 'address:patch', 'recipient:get'])]
    private ?bool $isPublic = false;

    public function __construct()
    {
        $this->deliveries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function isIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): static
    {
        $this->isMain = $isMain;

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

    public function getRecipient(): ?Recipient
    {
        return $this->recipient;
    }

    public function setRecipient(?Recipient $recipient): static
    {
        $this->recipient = $recipient;

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
            $delivery->setPickupAddress($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): static
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getPickupAddress() === $this) {
                $delivery->setPickupAddress(null);
            }
        }

        return $this;
    }

    public function getTownship(): ?Township
    {
        return $this->township;
    }

    public function setTownship(?Township $township): static
    {
        $this->township = $township;

        return $this;
    }

    public function isIsPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;

        return $this;
    }
}
