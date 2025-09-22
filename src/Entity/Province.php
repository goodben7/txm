<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProvinceRepository;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: ProvinceRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'province:get'], 
    operations:[
        new Get(
            security: 'is_granted("ROLE_PROVINCE_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_PROVINCE_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_PROVINCE_CREATE")',
            denormalizationContext: ['groups' => 'profile:post',],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_PROVINCE_UPDATE")',
            denormalizationContext: ['groups' => 'profile:patch',],
            processor: PersistProcessor::class,
        ),
    ]
)]
class Province implements RessourceInterface
{
    public const string ID_PREFIX = "PV";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[Groups(['province:get'])]
    #[ORM\Column(length: 16)]
    private ?string $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['province:get', 'profile:post', 'profile:patch'])]
    private ?string $label = null;

    #[ORM\Column]
    #[Groups(['province:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['province:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, City>
     */
    #[ORM\OneToMany(targetEntity: City::class, mappedBy: 'province')]
    #[Groups(['province:get'])]
    private Collection $cities;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
    }

    public function getId(): ?string
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

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): static
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
            $city->setProvince($this);
        }

        return $this;
    }

    public function removeCity(City $city): static
    {
        if ($this->cities->removeElement($city)) {
            // set the owning side to null (unless already changed)
            if ($city->getProvince() === $this) {
                $city->setProvince(null);
            }
        }

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
}
