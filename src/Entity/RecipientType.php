<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\RecipientTypeRepository;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: RecipientTypeRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => 'recipient_type:get'], 
    operations:[
        new Get(
            security: 'is_granted("ROLE_RECIPIENT_TYPE_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_RECIPIENT_TYPE_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_RECIPIENT_TYPE_CREATE")',
            denormalizationContext: ['groups' => 'recipient_type:post',],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_RECIPIENT_TYPE_UPDATE")',
            denormalizationContext: ['groups' => 'recipient_type:patch',],
            processor: PersistProcessor::class,
        ),
    ]
)]
class RecipientType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['recipient_type:get'])]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Groups(['recipient_type:get', 'recipient_type:post', 'recipient_type:patch'])]
    private ?string $label = null;

    #[ORM\Column(length: 120, nullable: true)]
    #[Groups(['recipient_type:get', 'recipient_type:post', 'recipient_type:patch'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['recipient_type:get', 'recipient_type:post', 'recipient_type:patch'])]
    private ?bool $actived = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
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
