<?php

namespace App\Entity;

use App\Doctrine\IdGenerator;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\DeliveryModelRepository;

#[ORM\Entity(repositoryClass: DeliveryModelRepository::class)]
class DeliveryModel
{
    const ID_PREFIX = "DM";

    const TYPE_PACKAGE = "P";
    const TYPE_MAIL = "M";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    private ?string $id = null;

    #[ORM\Column(length: 120)]
    private ?string $fullname = null;

    #[ORM\Column(length: 30)]
    private ?string $phone = null;

    #[ORM\Column(length: 1)]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $address = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 17, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $deliveryDate = null;

    #[ORM\Column(length: 16)]
    private ?string $createdBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $apikey = null;

    #[ORM\Column(length: 255)]
    private ?string $NumberMP = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $data1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $data2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $data3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $data4 = null;

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

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

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

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(\DateTimeImmutable $deliveryDate): static
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(string $createdBy): static
    {
        $this->createdBy = $createdBy;

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

    public function getApikey(): ?string
    {
        return $this->apikey;
    }

    public function setApikey(string $apikey): static
    {
        $this->apikey = $apikey;

        return $this;
    }

    public function getNumberMP(): ?string
    {
        return $this->NumberMP;
    }

    public function setNumberMP(string $NumberMP): static
    {
        $this->NumberMP = $NumberMP;

        return $this;
    }

    public function getData1(): ?string
    {
        return $this->data1;
    }

    public function setData1(?string $data1): static
    {
        $this->data1 = $data1;

        return $this;
    }

    public function getData2(): ?string
    {
        return $this->data2;
    }

    public function setData2(?string $data2): static
    {
        $this->data2 = $data2;

        return $this;
    }

    public function getData3(): ?string
    {
        return $this->data3;
    }

    public function setData3(?string $data3): static
    {
        $this->data3 = $data3;

        return $this;
    }

    public function getData4(): ?string
    {
        return $this->data4;
    }

    public function setData4(?string $data4): static
    {
        $this->data4 = $data4;

        return $this;
    }
}
