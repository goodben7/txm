<?php

namespace App\Entity;

use App\Enum\ServiceType;
use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ServiceRepository;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations:[
        new Get(
            security: 'is_granted("ROLE_SERVICE_DETAILS")',
            provider: ItemProvider::class,
            normalizationContext: ['groups' => 'service:get'], 
        ),
        new GetCollection(
            security: 'is_granted("ROLE_SERVICE_LIST")',
            provider: CollectionProvider::class,
            normalizationContext: ['groups' => 'service:list'], 
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
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'name' => 'ipartial',
    'type' => 'exact',
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
    #[Groups(['service:get', 'service:list'])]
    #[ORM\Column(length: 16)]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\NotNull()]
    #[Groups(['service:get', 'service:list', 'service:post', 'service:patch'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['service:get', 'service:list', 'service:post', 'service:patch'])]
    private ?string $description = null;

    #[ORM\Column(length: 15)]
    #[Assert\NotBlank()]
    #[Assert\NotNull()]
    #[Assert\Choice(callback: [ServiceType::class, 'getAll'], message: 'Invalid service type.')]
    #[Groups(['service:get', 'service:list',  'service:post', 'service:patch'])]
    private ?string $type = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Groups(['service:get', 'service:list', 'service:post', 'service:patch'])]
    private ?bool $active = null;

    #[ORM\Column]
    #[Groups(['service:get', 'service:list'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['service:get', 'service:list'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Store>
     */
    #[ORM\OneToMany(targetEntity: Store::class, mappedBy: 'service', cascade: ['all'])]
    #[Groups(['service:get', 'service:list'])]
    private Collection $stores;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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
}
