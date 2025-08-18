<?php

namespace App\Entity;

use App\Dto\CreateStoreDto;
use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\StoreRepository;
use App\State\CreateStoreProcessor;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: StoreRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'store:get'], 
    operations:[
        new Get(
            security: 'is_granted("ROLE_STORE_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_STORE_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_STORE_CREATE")',
            input: CreateStoreDto::class,
            processor: CreateStoreProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_STORE_UPDATE")',
            denormalizationContext: ['groups' => 'store:patch'],
            processor: PersistProcessor::class,
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'label' => 'ipartial',
    'active' => 'exact',
    'service' => 'exact',
    'customer' => 'exact'
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class Store implements RessourceInterface
{
    public const string ID_PREFIX = "ST";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(['store:get'])]
    private ?string $id = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Groups(['store:get', 'store:patch'])]
    private ?string $email = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(['store:get', 'store:patch'])]
    private ?string $phone = null;

    #[ORM\Column(length: 120)]
    #[Groups(['store:get', 'store:patch'])]
    private ?string $label = null;

    #[ORM\Column]
    #[Groups(['store:get', 'store:patch'])]
    private ?bool $active = null;

    #[ORM\Column]
    #[Groups(['store:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['store:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'stores')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['store:get'])]
    private ?Customer $customer = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['store:get', 'store:patch'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'stores')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['store:get', 'store:patch'])]
    private ?Service $service = null;

    public function getId(): ?string
    {
        return $this->id;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
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

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

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

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

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

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;

        return $this;
    }

    #[ORM\PreUpdate]
    public function updateUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
