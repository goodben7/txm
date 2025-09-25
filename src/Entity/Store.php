<?php

namespace App\Entity;

use App\Dto\CreateStoreDto;
use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use App\Dto\ValidateStoreDto;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\StoreRepository;
use App\State\CreateStoreProcessor;
use ApiPlatform\Metadata\ApiResource;
use App\State\ValidateStoreProcessor;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\HttpFoundation\File\File;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[Vich\Uploadable]
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
        new Post(
            uriTemplate: "stores/{id}/logo",
            denormalizationContext: ['groups' => 'store:logo'],
            security: 'is_granted("ROLE_STORE_UPDATE")',
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: PersistProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/stores/{id}/activations',
            security: 'is_granted("ROLE_STORE_ACTIVATE")',
            input: ValidateStoreDto::class,
            processor: ValidateStoreProcessor::class,
        )
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'label' => 'ipartial',
    'active' => 'exact',
    'service' => 'exact',
    'customer' => 'exact',
    'isVerified' => 'exact',
    'address' => 'exact',
    'city' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class Store implements RessourceInterface 
{
    public const string EVENT_STORE_VERIFIED = 'store.verified';
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

    #[Groups(groups: ['store:logo'])]
    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'filePath', size: 'fileSize')]
    private ?File $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['store:get'])]
    private ?string $filePath = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['store:get'])]
    private ?int $fileSize = null;

    #[Groups(groups: ['store:get'])]
    private ?string $contentUrl;

    #[Groups(groups: ['store:logo'])]
    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'filePathSecondary', size: 'fileSizeSecondary')]
    private ?File $fileSecondary = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['store:get'])]
    private ?string $filePathSecondary = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['store:get'])]
    private ?int $fileSizeSecondary = null;

    #[Groups(groups: ['store:get'])]
    private ?string $contentUrlSecondary;

    #[Groups(groups: ['store:get'])]
    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $isVerified = false;

    #[ORM\ManyToOne]
    #[Groups(['store:get', 'store:patch'])]
    private ?Address $address = null;

    #[ORM\ManyToOne]
    #[Groups(groups: ['store:get'])]
    private ?City $city = null;

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

    /**
     * Get the value of filePath
     */ 
    public function getFilePath(): string|null
    {
        return $this->filePath;
    }

    /**
     * Set the value of filePath
     *
     * @return  self
     */ 
    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Get the value of fileSize
     */ 
    public function getFileSize(): int|null
    {
        return $this->fileSize;
    }

    /**
     * Set the value of fileSize
     *
     * @return  self
     */ 
    public function setFileSize(?int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get the value of file
     */ 
    public function getFile(): File|null
    {
        return $this->file;
    }

    /**
     * Set the value of file
     *
     * @return  self
     */ 
    public function setFile($file): static
    {
        $this->file = $file;

        if (null !== $file) {
            $this->updatedAt = new \DateTimeImmutable('now');
        }

        return $this;
    }

    /**
     * Get the value of contentUrl
     */ 
    public function getContentUrl(): string|null
    {
        return $this->contentUrl;
    }

    /**
     * Set the value of contentUrl
     *
     * @return  self
     */ 
    public function setContentUrl($contentUrl): static
    {
        $this->contentUrl = $contentUrl;

        return $this;
    }

    /**
     * Get the value of file
     */ 
    public function getFileSecondary()
    {
        return $this->fileSecondary;
    }

    /**
     * Set the value of file
     *
     * @return  self
     */ 
    public function setFileSecondary($fileSecondary)
    {
        $this->fileSecondary = $fileSecondary;

        if (null !== $fileSecondary) {
            $this->updatedAt = new \DateTimeImmutable('now');
        }

        return $this;
    }

    public function getFilePathSecondary(): ?string
    {
        return $this->filePathSecondary;
    }

    public function setFilePathSecondary(?string $filePathSecondary): self
    {
        $this->filePathSecondary = $filePathSecondary;

        return $this;
    }

    public function getFileSizeSecondary(): ?int
    {
        return $this->fileSizeSecondary;
    }

    public function setFileSizeSecondary(?int $fileSizeSecondary): self
    {
        $this->fileSizeSecondary = $fileSizeSecondary;

        return $this;
    }

    /**
     * Get the value of contentUrlSecondary
     */ 
    public function getContentUrlSecondary(): ?string
    {
        return $this->contentUrlSecondary;
    }

    /**
     * Set the value of contentUrlSecondary
     *
     * @return  self
     */ 
    public function setContentUrlSecondary(?string $contentUrlSecondary): self
    {
        $this->contentUrlSecondary = $contentUrlSecondary;

        return $this;
    }

    /**
     * Get the value of isVerified
     */ 
    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * Set the value of isVerified
     *
     * @return  self
     */ 
    public function setIsVerified(?bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }
    
    #[ORM\PrePersist]
    public function setCityFromAddress(): void
    {
        if ($this->address !== null) {
            $city = $this->address->getCity();
            if ($city !== null) {
                $this->city = $city;
            }
        }
    }
    
    #[ORM\PreUpdate]
    public function updateCityFromAddress(): void
    {
        $this->city = $this->address?->getCity() ?: null;
    }
}
