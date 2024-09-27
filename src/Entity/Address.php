<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\AddressRepository;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => 'address:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_ADDRESS_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_ADDRESS_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_ADDRESS_CREATE")',
            denormalizationContext: ['groups' => 'address:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_ADDRESS_UPDATE")',
            denormalizationContext: ['groups' => 'address:patch'],
            processor: PersistProcessor::class,
        ),
    ]
)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['customer:get', 'address:get'])]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    #[Groups(groups: ['customer:get', 'address:get', 'address:post', 'address:patch'])]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(groups: ['customer:get', 'address:get', 'address:post', 'address:patch'])]
    private ?string $address = null;

    #[ORM\Column]
    #[Groups(groups: ['customer:get', 'address:get', 'address:post', 'address:patch'])]
    private ?bool $isMain = false;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['address:post'])]
    private ?Customer $customer = null;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function isIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): static
    {
        $this->isMain = $isMain;

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
}
