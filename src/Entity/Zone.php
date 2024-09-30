<?php

namespace App\Entity;

use App\Dto\CreateZoneDto;
use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ZoneRepository;
use App\State\CreateZoneProcessor;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => 'zone:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_ZONE_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_ZONE_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_ZONE_CREATE")',
            input: CreateZoneDto::class,
            processor: CreateZoneProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_ZONE_UPDATE")',
            denormalizationContext: ['groups' => 'zone:patch'],
            processor: PersistProcessor::class,
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'label' => 'ipartial',
    'description' => 'ipartial',
    'actived' => 'exact'
])]
class Zone
{
    const ID_PREFIX = "ZO";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(groups: ['zone:get', 'township:get'])]
    private ?string $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(groups: ['zone:get', 'zone:patch', 'township:get'])]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(groups: ['zone:get', 'zone:patch', 'township:get'])]
    private ?string $description = '-';

    #[ORM\Column]
    #[Groups(groups: ['zone:get', 'zone:patch'])]
    private ?bool $actived = true;

    /**
     * @var Collection<int, Township>
     */
    #[ORM\OneToMany(targetEntity: Township::class, mappedBy: 'zone', cascade: ['all'])]
    #[Groups(groups: ['zone:get'])]
    private Collection $townships;

    public function __construct()
    {
        $this->townships = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isActived(): ?bool
    {
        return $this->actived;
    }

    public function setActived(bool $actived): static
    {
        $this->actived = $actived;

        return $this;
    }

    /**
     * @return Collection<int, Township>
     */
    public function getTownships(): Collection
    {
        return $this->townships;
    }

    public function addTownship(Township $township): static
    {
        if (!$this->townships->contains($township)) {
            $this->townships->add($township);
            $township->setZone($this);
        }

        return $this;
    }

    public function removeTownship(Township $township): static
    {
        if ($this->townships->removeElement($township)) {
            // set the owning side to null (unless already changed)
            if ($township->getZone() === $this) {
                $township->setZone(null);
            }
        }

        return $this;
    }
}
