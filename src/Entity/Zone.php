<?php

namespace App\Entity;

use App\Doctrine\IdGenerator;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ZoneRepository;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
class Zone
{
    const ID_PREFIX = "ZO";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    private ?string $id = null;

    #[ORM\Column(length: 30)]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $actived = null;

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
