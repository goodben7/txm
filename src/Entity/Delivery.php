<?php

namespace App\Entity;

use App\Doctrine\IdGenerator;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\DeliveryRepository;

#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
class Delivery
{
    const ID_PREFIX = "DE";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    private ?string $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $pickupAddress = null;

    #[ORM\Column(length: 15)]
    private ?string $senderPhone = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $deliveryAddress = null;

    #[ORM\Column(length: 15)]
    private ?string $recipientPhone = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $deliveryDate = null;

    #[ORM\Column(length: 1)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 1)]
    private ?string $type = null;

    #[ORM\Column(length: 120)]
    private ?string $township = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Zone $zone = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recipient $recipient = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\Column(length: 16)]
    private ?string $createdBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $updatedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $validatedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $pickupedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $pickupedAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $inprogressBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $inprogressAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $canceledBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $canceledAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $DelayedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $DelayedAt = null;

    #[ORM\Column(length: 16)]
    private ?string $trackingNumber = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPickupAddress(): ?string
    {
        return $this->pickupAddress;
    }

    public function setPickupAddress(string $pickupAddress): static
    {
        $this->pickupAddress = $pickupAddress;

        return $this;
    }

    public function getSenderPhone(): ?string
    {
        return $this->senderPhone;
    }

    public function setSenderPhone(string $senderPhone): static
    {
        $this->senderPhone = $senderPhone;

        return $this;
    }

    public function getDeliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(string $deliveryAddress): static
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getRecipientPhone(): ?string
    {
        return $this->recipientPhone;
    }

    public function setRecipientPhone(string $recipientPhone): static
    {
        $this->recipientPhone = $recipientPhone;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTownship(): ?string
    {
        return $this->township;
    }

    public function setTownship(string $township): static
    {
        $this->township = $township;

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

    public function getRecipient(): ?Recipient
    {
        return $this->recipient;
    }

    public function setRecipient(?Recipient $recipient): static
    {
        $this->recipient = $recipient;

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

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

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

    public function getValidatedBy(): ?string
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(?string $validatedBy): static
    {
        $this->validatedBy = $validatedBy;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeImmutable $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getPickupedBy(): ?string
    {
        return $this->pickupedBy;
    }

    public function setPickupedBy(?string $pickupedBy): static
    {
        $this->pickupedBy = $pickupedBy;

        return $this;
    }

    public function getPickupedAt(): ?\DateTimeImmutable
    {
        return $this->pickupedAt;
    }

    public function setPickupedAt(?\DateTimeImmutable $pickupedAt): static
    {
        $this->pickupedAt = $pickupedAt;

        return $this;
    }

    public function getInprogressBy(): ?string
    {
        return $this->inprogressBy;
    }

    public function setInprogressBy(?string $inprogressBy): static
    {
        $this->inprogressBy = $inprogressBy;

        return $this;
    }

    public function getInprogressAt(): ?\DateTimeImmutable
    {
        return $this->inprogressAt;
    }

    public function setInprogressAt(?\DateTimeImmutable $inprogressAt): static
    {
        $this->inprogressAt = $inprogressAt;

        return $this;
    }

    public function getCanceledBy(): ?string
    {
        return $this->canceledBy;
    }

    public function setCanceledBy(?string $canceledBy): static
    {
        $this->canceledBy = $canceledBy;

        return $this;
    }

    public function getCanceledAt(): ?\DateTimeImmutable
    {
        return $this->canceledAt;
    }

    public function setCanceledAt(?\DateTimeImmutable $canceledAt): static
    {
        $this->canceledAt = $canceledAt;

        return $this;
    }

    public function getDelayedBy(): ?string
    {
        return $this->DelayedBy;
    }

    public function setDelayedBy(?string $DelayedBy): static
    {
        $this->DelayedBy = $DelayedBy;

        return $this;
    }

    public function getDelayedAt(): ?\DateTimeImmutable
    {
        return $this->DelayedAt;
    }

    public function setDelayedAt(?\DateTimeImmutable $DelayedAt): static
    {
        $this->DelayedAt = $DelayedAt;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(string $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }
}
