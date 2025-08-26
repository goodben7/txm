<?php

namespace App\Entity;

use App\Enum\ServiceType;
use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ServiceRepository;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\HttpFoundation\File\File;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;


#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'service:get'], 
    operations:[
        new Get(
            security: 'is_granted("ROLE_SERVICE_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_SERVICE_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            security: 'is_granted("ROLE_SERVICE_CREATE")',
            denormalizationContext: ['groups' => 'service:post',],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_SERVICE_UPDATE")',
            denormalizationContext: ['groups' => 'service:patch',],
            processor: PersistProcessor::class,
        ),
        new Post(
            uriTemplate: "services/{id}/logo",
            denormalizationContext: ['groups' => 'service:logo'],
            security: 'is_granted("ROLE_SERVICE_UPDATE")',
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: PersistProcessor::class,
            status: 200
        )
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'name' => 'ipartial',
    'active' => 'exact'
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class Service implements RessourceInterface 
{
    public const string ID_PREFIX = "SE";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[Groups(['service:get'])]
    #[ORM\Column(length: 16)]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\NotNull()]
    #[Groups(['service:get', 'service:post', 'service:patch'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['service:get', 'service:post', 'service:patch'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Groups(['service:get', 'service:post', 'service:patch'])]
    private ?bool $active = null;

    #[ORM\Column]
    #[Groups(['service:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['service:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Store>
     */
    #[ORM\OneToMany(targetEntity: Store::class, mappedBy: 'service', cascade: ['all'])]
    #[Groups(['service:get'])]
    private Collection $stores;

    #[Groups(groups: ['service:logo'])]
    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'filePath', size: 'fileSize')]
    private ?File $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['service:get'])]
    private ?string $filePath = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['service:get'])]
    private ?int $fileSize = null;

    #[Groups(groups: ['service:get'])]
    private ?string $contentUrl;

    #[Groups(groups: ['service:logo'])]
    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'filePathSecondary', size: 'fileSizeSecondary')]
    private ?File $fileSecondary = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['service:get'])]
    private ?string $filePathSecondary = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['service:get'])]
    private ?int $fileSizeSecondary = null;

    #[Groups(groups: ['service:get'])]
    private ?string $contentUrlSecondary;

    public function __construct()
    {
        $this->stores = new ArrayCollection();
    }

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

    public function isActive(): ?bool
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

    #[ORM\PreUpdate]
    public function updateUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function buildCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
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
            $store->setService($this);
        }

        return $this;
    }

    public function removeStore(Store $store): static
    {
        if ($this->stores->removeElement($store)) {
            // set the owning side to null (unless already changed)
            if ($store->getService() === $this) {
                $store->setService(null);
            }
        }

        return $this;
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
}
