<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use App\Dto\RejectDocumentDto;
use ApiPlatform\Metadata\Delete;
use App\Dto\ValidateDocumentDto;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\DocumentRepository;
use App\State\RejectDocumentProcessor;
use ApiPlatform\Metadata\GetCollection;
use App\State\ValidateDocumentProcessor;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\HttpFoundation\File\File;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'doc:get'],
    operations: [
        new Get(
            security: 'is_granted("ROLE_DOC_DETAILS")',
            provider: ItemProvider::class,
        ),
        new GetCollection(
            security: 'is_granted("ROLE_DOC_LIST")',
            provider: CollectionProvider::class,
        ),
        new Post(
            denormalizationContext: ['groups' => 'doc:post'],
            security: 'is_granted("ROLE_DOC_CREATE")',
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: PersistProcessor::class,
        ),
        new Post(
            uriTemplate: '/documents/rejections',
            security: 'is_granted("ROLE_DOC_REJECT")',
            input: RejectDocumentDto::class,
            processor: RejectDocumentProcessor::class,
            status: 200
        ),
        new Post(
            uriTemplate: '/documents/validations',
            security: 'is_granted("ROLE_DOC_VALIDATE")',
            input: ValidateDocumentDto::class,
            processor: ValidateDocumentProcessor::class,
            status: 200
        ),
        new Delete(
            security: 'is_granted("ROLE_DOC_DELETE")',
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'holderId' => 'exact',
    'type' => 'exact',
    'status' => 'exact',
    'documentRefNumber' => 'start',
    'validatedBy' => 'exact',
    'rejectedBy' => 'exact'
])]
#[ApiFilter(OrderFilter::class, properties: ['uploadedAt', 'updatedAt', 'validatedAt', 'rejectedAt'])]
#[ApiFilter(DateFilter::class, properties: ['uploadedAt', 'updatedAt', 'validatedAt', 'rejectedAt'])]
class Document implements RessourceInterface
{
    public const string ID_PREFIX = "DO";

    public const string TYPE_ID = "ID";
    public const string TYPE_PASSPORT = "PASS";
    public const string TYPE_PASSPORT_PHOTO = "PSSPH";
    public const string TYPE_DRIVE_LICENCE = "DRVLC";
    public const string TYPE_SIGNATURE = "SIGNT";
    public const string TYPE_LEGAL_DOCUMENT = "LGDOC";
    public const string TYPE_OTHER = "OTHER";

    public const string STATUS_VALIDATED = "V";
    public const string STATUS_PENDING = "P";
    public const string STATUS_REFUSED = "R";

    public const string EVENT_DOCUMENT_CREATED = "created";
    public const string EVENT_DOCUMENT_REJECT = "rejected";
    public const string EVENT_DOCUMENT_VALIDATE = "validated";

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(groups: ['doc:get'])]
    private ?string $id = null;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Choice(callback: "getTypeAsChoices")]
    #[ORM\Column(length: 10)]
    #[Groups(groups: ['doc:get', 'doc:post'])]
    private ?string $type = null;

    #[Groups(groups: ['doc:get', 'doc:post'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $documentRefNumber = null;

    #[ORM\Column()]
    #[Groups(groups: ['doc:get'])]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\Column(length: 1)]
    #[Groups(groups: ['doc:get'])]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['doc:get'])]
    private ?string $filePath = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['doc:get'])]
    private ?int $fileSize = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['doc:get'])]
    private ?string $rejectionReason = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(groups: ['doc:get', 'doc:post'])]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['doc:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['doc:get'])]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['doc:get'])]
    private ?string $validatedBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['doc:get'])]
    private ?\DateTimeImmutable $rejectedAt = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(groups: ['doc:get'])]
    private ?string $rejectedBy = null;

    #[ORM\Column(length: 16)]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Groups(groups: ['doc:get', 'doc:post'])]
    private ?string $holderId = null;

    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Groups(groups: ['doc:post'])]
    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'filePath', size: 'fileSize')]
    private ?File $file = null;

    #[Groups(groups: ['doc:get'])]
    private ?string $contentUrl;

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the value of file
     */ 
    public function getFile(): File|null
    {
        return $this->file;
    }

    /**
     * Set the value of file
     *
     * @return  self
     */ 
    public function setFile($file): static
    {
        $this->file = $file;

        if (null !== $file) {
            $this->updatedAt = new \DateTimeImmutable('now');
        }

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

    public function getDocumentRefNumber(): ?string
    {
        return $this->documentRefNumber;
    }

    public function setDocumentRefNumber(?string $documentRefNumber): static
    {
        $this->documentRefNumber = $documentRefNumber;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(?\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

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

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

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

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeImmutable $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

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

    public function getRejectedAt(): ?\DateTimeImmutable
    {
        return $this->rejectedAt;
    }

    public function setRejectedAt(?\DateTimeImmutable $rejectedAt): static
    {
        $this->rejectedAt = $rejectedAt;

        return $this;
    }

    public function getRejectedBy(): ?string
    {
        return $this->rejectedBy;
    }

    public function setRejectedBy(?string $rejectedBy): static
    {
        $this->rejectedBy = $rejectedBy;

        return $this;
    }

    public function getHolderId(): ?string
    {
        return $this->holderId;
    }

    public function setHolderId(string $holderId): static
    {
        $this->holderId = $holderId;

        return $this;
    }

    /**
     * Get the value of filePath
     */ 
    public function getFilePath(): string|null
    {
        return $this->filePath;
    }

    /**
     * Set the value of filePath
     *
     * @return  self
     */ 
    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Get the value of fileSize
     */ 
    public function getFileSize(): int|null
    {
        return $this->fileSize;
    }

    /**
     * Set the value of fileSize
     *
     * @return  self
     */ 
    public function setFileSize(?int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get the value of contentUrl
     */ 
    public function getContentUrl(): string|null
    {
        return $this->contentUrl;
    }

    /**
     * Set the value of contentUrl
     *
     * @return  self
     */ 
    public function setContentUrl($contentUrl): static
    {
        $this->contentUrl = $contentUrl;

        return $this;
    }

    public static function getTypeAsChoices(): array {
        return [
            "Pièce d'identité" => self::TYPE_ID,
            "Permis de conduire" => self::TYPE_DRIVE_LICENCE,
            "Passport" => self::TYPE_PASSPORT,
            "Photo Passport" => self::TYPE_PASSPORT_PHOTO,
            "Signature" => self::TYPE_SIGNATURE,
            "Document légal" => self::TYPE_LEGAL_DOCUMENT,
            "Autres" => self::TYPE_OTHER,
        ];
    }

    public static function getStatusAsChoices(): array {
        return [
            "Validé" => self::STATUS_VALIDATED,
            "En attente" => self::STATUS_PENDING,
            "Réfusé" => self::STATUS_REFUSED,
        ];
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->uploadedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function updateUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
