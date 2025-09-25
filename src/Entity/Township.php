<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\TownshipRepository;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: TownshipRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => 'township:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_TOWNSHIP_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_TOWNSHIP_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_TOWNSHIP_CREATE")',
            denormalizationContext: ['groups' => 'township:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_TOWNSHIP_UPDATE")',
            denormalizationContext: ['groups' => 'township:patch'],
            processor: PersistProcessor::class,
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'label' => 'ipartial',
    'zone' => 'exact',
    'city' => 'exact',
])]
class Township
{
    
    const ID_PREFIX = "TO";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(groups: ['zone:get', 'township:get', 'address:get', 'recipient:get'])]
    private ?string $id = null;

    #[ORM\Column(length: 30)]
    #[Groups(groups: ['zone:get', 'township:get', 'township:post', 'township:patch', 'address:get', 'recipient:get'])]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'townships')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['township:get', 'township:post', 'township:patch'])]
    private ?Zone $zone = null;

    /**
     * @var Collection<int, Address>
     */
    #[ORM\OneToMany(targetEntity: Address::class, mappedBy: 'township')]
    private Collection $addresses;

    #[ORM\ManyToOne(inversedBy: 'townships')]
    #[Groups(groups: ['township:get', 'township:post', 'township:patch'])]
    private ?City $city = null;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
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

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): static
    {
        $this->zone = $zone;

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
            $address->setTownship($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): static
    {
        if ($this->addresses->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getTownship() === $this) {
                $address->setTownship(null);
            }
        }

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
}
