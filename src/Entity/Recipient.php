<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\RecipientRepository;
use App\State\DeleteRecipientProcessor;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RecipientRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PHONE', fields: ['phone'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'recipient:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_RECIPIENT_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_RECIPIENT_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_RECIPIENT_CREATE")',
            denormalizationContext: ['groups' => 'recipient:post'],
            processor: PersistProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_RECIPIENT_UPDATE")',
            denormalizationContext: ['groups' => 'recipient:patch'],
            processor: PersistProcessor::class,
        ),
        new Delete(
            security: 'is_granted("ROLE_RECIPIENT_DELETE")',
            processor: DeleteRecipientProcessor::class
        )
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'fullname' => 'ipartial',
    'phone' => 'ipartial',
    'email' => 'ipartial',
    'deleted' => 'exact'
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class Recipient
{
    const ID_PREFIX = "RE";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(groups: ['recipient:get'])]
    private ?string $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(groups: ['recipient:get', 'recipient:post', 'recipient:patch'])]
    private ?string $fullname = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(groups: ['recipient:get', 'recipient:post', 'recipient:patch'])]
    private ?string $phone = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    #[Groups(groups: ['recipient:get', 'recipient:post', 'recipient:patch'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(groups: ['recipient:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['recipient:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(groups: ['recipient:get'])]
    private ?bool $deleted = false;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

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
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }
}
