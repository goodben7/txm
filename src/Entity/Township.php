<?php

namespace App\Entity;

use App\Doctrine\IdGenerator;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TownshipRepository;

#[ORM\Entity(repositoryClass: TownshipRepository::class)]
class Township
{
    
    const ID_PREFIX = "TO";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    private ?string $id = null;

    #[ORM\Column(length: 30)]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'townships')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Zone $zone = null;

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
}
