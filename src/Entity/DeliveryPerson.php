<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use App\Dto\CreateDeliveryPersonDto;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\DeliveryPersonRepository;
use App\State\CreateDeliveryPersonProcessor;
use App\State\DeleteDeliveryPersonProcessor;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: DeliveryPersonRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PHONE', fields: ['phone'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USER', fields: ['userId'])]
#[ApiResource(
    normalizationContext: ['groups' => 'delivery_person:get'], 
    operations:[
        new Get(
            security: 'is_granted("ROLE_DELIVERY_PERSON_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_DELIVERY_PERSON_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_DELIVERY_PERSON_CREATE")',
            input: CreateDeliveryPersonDto::class,
            processor: CreateDeliveryPersonProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_DELIVERY_PERSON_UPDATE")',
            denormalizationContext: ['groups' => 'delivery_person:patch'],
            processor: PersistProcessor::class,
        ),
        new Delete(
            security: 'is_granted("ROLE_DELIVERY_PERSON_DELETE")',
            processor: DeleteDeliveryPersonProcessor::class
        )
    ]
)]
class DeliveryPerson implements RessourceInterface
{
    public const string ID_PREFIX = "DE";

    public const string VEHICLE_TYPE_CAR = 'CAR';
    public const string VEHICLE_TYPE_TRUCK = 'TRUCK';
    public const string VEHICLE_TYPE_MOTORCYCLE = 'MOTORCYCLE';
    public const string VEHICLE_TYPE_BICYCLE = 'BICYCLE';
    public const string VEHICLE_TYPE_OTHER = 'OTHER';

    public const string STATUS_ACTIVE = 'A';
    public const string STATUS_INACTIVE = 'I';   

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(['delivery_person:get'])]
    private ?string $id = null;

    #[ORM\Column(length: 120)]
    #[Groups(['delivery_person:get', 'delivery_person:patch'])]
    private ?string $fullname = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(['delivery_person:get', 'delivery_person:patch'])]
    private ?string $phone = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Groups(['delivery_person:get', 'delivery_person:patch'])]
    private ?string $email = null;

    #[ORM\Column(length: 1)]
    #[Groups(['delivery_person:get', 'delivery_person:patch'])]
    private ?string $status = null;

    #[ORM\Column(length: 60, nullable: true)]
    #[Groups(['delivery_person:get', 'delivery_person:patch'])]
    private ?string $vehicleType = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['delivery_person:get', 'delivery_person:patch'])]
    private ?string $licenseNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['delivery_person:get', 'delivery_person:patch'])]
    private ?string $vehicleLicensePlate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['delivery_person:get'])]
    private ?string $identificationNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['delivery_person:get'])]
    private ?string $identificationPhoto = null;

    #[ORM\Column(length: 2, nullable: true)]
    #[Groups(['delivery_person:get', 'delivery_person:patch'])]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['delivery_person:get', 'delivery_person:patch'])]
    private ?string $address = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['delivery_person:get', 'delivery_person:patch'])]
    private ?\DateTimeImmutable $dateOfBirth = null;

    #[ORM\Column(length: 120, nullable: true)]
    #[Groups(['delivery_person:get', 'delivery_person:patch'])]
    private ?string $city = null;

    #[ORM\Column]
    #[Groups(['delivery_person:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['delivery_person:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['delivery_person:get'])]
    private ?bool $deleted = false;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(['delivery_person:get'])]
    private ?string $userId = null;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getVehicleType(): ?string
    {
        return $this->vehicleType;
    }

    public function setVehicleType(?string $vehicleType): static
    {
        $this->vehicleType = $vehicleType;

        return $this;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(?string $licenseNumber): static
    {
        $this->licenseNumber = $licenseNumber;

        return $this;
    }

    public function getVehicleLicensePlate(): ?string
    {
        return $this->vehicleLicensePlate;
    }

    public function setVehicleLicensePlate(?string $vehicleLicensePlate): static
    {
        $this->vehicleLicensePlate = $vehicleLicensePlate;

        return $this;
    }

    public function getIdentificationNumber(): ?string
    {
        return $this->identificationNumber;
    }

    public function setIdentificationNumber(?string $identificationNumber): static
    {
        $this->identificationNumber = $identificationNumber;

        return $this;
    }

    public function getIdentificationPhoto(): ?string
    {
        return $this->identificationPhoto;
    }

    public function setIdentificationPhoto(?string $identificationPhoto): static
    {
        $this->identificationPhoto = $identificationPhoto;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeImmutable $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

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

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}
