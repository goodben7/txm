<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ZoneRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
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
            denormalizationContext: ['groups' => 'zone:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_ZONE_UPDATE")',
            denormalizationContext: ['groups' => 'zone:patch'],
            processor: PersistProcessor::class,
        ),
    ]
)]
class Zone
{
    const ID_PREFIX = "ZO";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(groups: ['zone:get'])]
    private ?string $id = null;

    #[ORM\Column(length: 30)]
    #[Groups(groups: ['zone:get', 'zone:post', 'zone:patch'])]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(groups: ['zone:get', 'zone:post', 'zone:patch'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(groups: ['zone:get', 'zone:post', 'zone:patch'])]
    private ?bool $actived = true;

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
}
