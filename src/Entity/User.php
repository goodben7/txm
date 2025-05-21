<?php

namespace App\Entity;

use App\Dto\CreateUserDto;
use ApiPlatform\Metadata\Get;
use App\Doctrine\IdGenerator;
use ApiPlatform\Metadata\Post;
use App\Dto\ChangePasswordDto;
use App\Dto\SetUserProfileDto;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Model\UserProxyIntertace;
use App\Manager\PermissionManager;
use App\Repository\UserRepository;
use App\State\CreateUserProcessor;
use App\State\DeleteUserProcessor;
use App\State\SetProfileProcessor;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\State\ToggleLockUserProcessor;
use ApiPlatform\Metadata\GetCollection;
use App\State\ChangeUserPasswordProcessor;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PHONE', fields: ['phone'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => 'user:get'], 
    operations:[
        new Get(
            security: 'is_granted("ROLE_USER_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_USER_LIST")',
            provider: CollectionProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_USER_CREATE")',
            input: CreateUserDto::class,
            processor: CreateUserProcessor::class,
        ),
        new Patch(
            uriTemplate: "users/{id}/credentials",
            security: 'is_granted("ROLE_USER_CHANGE_PWD", object)',
            input: ChangePasswordDto::class,
            processor: ChangeUserPasswordProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_USER_EDIT")',
            denormalizationContext: ['groups' => 'user:patch'],
            processor: PersistProcessor::class,
        ),
        new Delete(
            security: 'is_granted("ROLE_USER_DELETE")',
            processor: DeleteUserProcessor::class
        ),
        new Post(
            uriTemplate: "users/{id}/lock_toggle",
            status: 200,
            denormalizationContext: ['groups' => 'user:lock'],
            security: 'is_granted("ROLE_USER_LOCK")',
            processor: ToggleLockUserProcessor::class
        ),
        new Post(
            uriTemplate: "users/{id}/profiles",
            security: 'is_granted("ROLE_USER_SET_PROFILE")',
            normalizationContext: ['groups' => 'user:get'],
            input: SetUserProfileDto::class,
            processor: SetProfileProcessor::class,
            status: 200,
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'email' => 'exact',
    'roles' => 'exact',
    'phone' => 'exact',
    'displayName' => 'ipartial',
    'deleted' => 'exact',
    'profile' => 'exact',
    'locked' => 'exact'
])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    const ID_PREFIX = "US";
    const ROLE_ADMIN = 'ROLE_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue( strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(IdGenerator::class)]
    #[ORM\Column(length: 16)]
    #[Groups(groups: ['user:get'])]
    private ?string $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(groups: ['user:get'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(groups: ['user:get'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    private ?string $plainPassword;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(groups: ['user:get', 'user:patch'])]
    private ?string $phone = null;

    #[ORM\Column(length: 120, nullable: true)]
    #[Groups(groups: ['user:get', 'user:patch'])]
    private ?string $displayName = null;

    #[ORM\Column]
    #[Groups(groups: ['user:get'])]
    private ?bool $deleted = false;

    #[ORM\Column]
    #[Groups(groups: ['user:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['user:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(groups: ['user:get'])]
    private ?bool $locked = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['user:get'])]
    private ?Profile $profile = null;

    #[ORM\Column(length: 8, nullable: true)]
    #[Groups(['user:get'])]
    private ?string $personType = null;
    
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        $roles[] = $this->getPersonRole();

        if (UserProxyIntertace::PERSON_ADMIN === $this->personType) {
            $roles = array_merge($roles, array_values((array)PermissionManager::getInstance()->getPermissionsAsListChoices()));
        } elseif (null !== $this->profile) {
            $roles = array_merge($roles, $this->profile->getPermissions());
        }
        
        return array_unique($roles);
    
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = $displayName;

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

    public static function getAvailablesRoles(): array 
    {
        return [ 
            'Administrateur' => self::ROLE_ADMIN
        ];
    }

    #[ORM\PreUpdate]
    public function updateUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
    

    /**
     * Get the value of plainPassword
     */ 
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set the value of plainPassword
     *
     * @return  self
     */ 
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Get the value of locked
     */ 
    public function isLocked(): bool|null
    {
        return $this->locked;
    }

    /**
     * Set the value of locked
     *
     * @return  self
     */ 
    public function setLocked($locked): static
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get the value of profile
     */ 
    public function getProfile(): Profile|null
    {
        return $this->profile;
    }

    /**
     * Set the value of profile
     *
     * @return  self
     */ 
    public function setProfile($profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get the value of personType
     */ 
    public function getPersonType(): string|null
    {
        return $this->personType;
    }

    /**
     * Set the value of personType
     *
     * @return  self
     */ 
    public function setPersonType($personType): static
    {
        $this->personType = $personType;

        return $this;
    }

    private function getPersonRole(): string
    {
        return array_search($this->personType, [
            "ROLE_DLV_PRS" => UserProxyIntertace::PERSON_DLV_PRS,
            "ROLE_ADMIN" => UserProxyIntertace::PERSON_ADMIN,
            "ROLE_SENDER" => UserProxyIntertace::PERSON_SENDER,
        ]);
    }

    public static function getAcceptedPersonList(): array
    {
        return [
            UserProxyIntertace::PERSON_SENDER,
            UserProxyIntertace::PERSON_DLV_PRS,
            UserProxyIntertace::PERSON_ADMIN
        ];
    }

    public static function getPersonTypesAsChoices(): array
    {
        return [
            "Expéditeur" => UserProxyIntertace::PERSON_SENDER,
            "Livreur" => UserProxyIntertace::PERSON_DLV_PRS,
            "Admin" => UserProxyIntertace::PERSON_ADMIN,
        ];
    }

    public static function getPersonTypesAsList(): array
    {
        return array_values(self::getPersonTypesAsChoices());
    }
}
