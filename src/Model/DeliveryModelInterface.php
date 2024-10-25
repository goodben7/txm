<?php

namespace App\Model;

use App\Entity\Customer;

interface DeliveryModelInterface
{
    public function getId(): ?string;

    public function getFullname(): ?string;

    public function setFullname(string $fullname): static;

    public function getPhone(): ?string;

    public function setPhone(string $phone): static;

    public function getType(): ?string;

    public function setType(string $type): static;

    public function getAddress(): ?string;

    public function setAddress(string $address): static;

    public function getDescription(): ?string;

    public function setDescription(?string $description): static;

    public function getAmount(): ?string;

    public function setAmount(string $amount): static;

    public function getDeliveryDate(): ?\DateTimeImmutable;

    public function setDeliveryDate(\DateTimeImmutable $deliveryDate): static;

    public function getCreatedBy(): ?string;

    public function setCreatedBy(string $createdBy): static;

    public function getCreatedAt(): ?\DateTimeImmutable;

    public function setCreatedAt(\DateTimeImmutable $createdAt): static;

    public function getApikey(): ?string;

    public function setApikey(string $apikey): static;

    public function getNumberMP(): ?string;

    public function setNumberMP(string $numberMP): static;

    public function getData1(): ?string;

    public function setData1(?string $data1): static;

    public function getData2(): ?string;

    public function setData2(?string $data2): static;

    public function getData3(): ?string;

    public function setData3(?string $data3): static;

    public function getData4(): ?string;

    public function setData4(?string $data4): static;

    public function getCustomer(): ?Customer;

    public function setCustomer(?Customer $customer): static;
}
