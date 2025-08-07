<?php

namespace App\Entity;

use App\Repository\OTPRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OTPRepository::class)]
class OTP
{
    public const string TYPE_REGISTRATION = "registration";      
    public const string TYPE_LOGIN = "login";             
    public const string TYPE_PASSWORD_RESET = "password_reset";    
    public const string TYPE_EMAIL_CHANGE = "email_change";     
    public const string TYPE_PHONE_CHANGE = "phone_change";    

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expiryDate = null;

    #[ORM\Column(length: 6)]
    private ?string $code = null;

    #[ORM\Column]
    private ?bool $send = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getExpiryDate(): ?\DateTimeImmutable
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(\DateTimeImmutable $expiryDate): static
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function isSend(): ?bool
    {
        return $this->send;
    }

    public function setSend(bool $send): static
    {
        $this->send = $send;

        return $this;
    }
}
