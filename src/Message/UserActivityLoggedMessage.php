<?php
namespace App\Message;

class UserActivityLoggedMessage
{
    public function __construct(
        private string             $user,
        private \DateTimeImmutable $date,
        private string             $activity,
        private string             $ressourceName,
        private ?string             $ressourceIdentifier = null,
    )
    {
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getActivity(): string
    {
        return $this->activity;
    }

    public function getRessourceName(): string
    {
        return $this->ressourceName;
    }

    public function getRessourceIdentifier(): ?string
    {
        return $this->ressourceIdentifier;
    }
}
