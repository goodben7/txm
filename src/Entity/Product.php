<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use App\Dto\CreateProductDto;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProductRepository;
use App\State\CreateProductProcessor;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'product:get'], 
    operations:[
        new Get(
            security: 'is_granted("ROLE_PRODUCT_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_PRODUCT_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_PRODUCT_CREATE")',
            input: CreateProductDto::class,
            processor: CreateProductProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_PRODUCT_UPDATE")',
            denormalizationContext: ['groups' => 'product:patch'],
            processor: PersistProcessor::class,
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'name' => 'ipartial',
    'type' => 'exact',
    'active' => 'exact',
    'store' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class Product implements RessourceInterface
{
    public const string ID_PREFIX = "PD";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(['product:get'])]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:get', 'product:patch'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['product:get', 'product:patch'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 17, scale: 2)]
    #[Groups(['product:get', 'product:patch'])]
    private ?string $price = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['product:get', 'product:patch'])]
    private ?Store $store = null;

    #[ORM\Column]
    #[Groups(['product:get', 'product:patch'])]
    private ?bool $active = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['product:get', 'product:patch'])]
    private ?string $type = null;

    #[ORM\Column]
    #[Groups(['product:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['product:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

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

    #[ORM\PreUpdate]
    public function updateUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}