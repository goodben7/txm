<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use App\Repository\ProductOptionValueRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: ProductOptionValueRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => 'product_option_value:get'], 
    operations:[
        new Get(
            security: 'is_granted("ROLE_PRODUCT_DETAILS")',
            provider: ItemProvider::class
        ),
        new GetCollection(
            security: 'is_granted("ROLE_PRODUCT_LIST")',
            provider: CollectionProvider::class
        ),
        new Patch(
            security: 'is_granted("ROLE_PRODUCT_UPDATE")',
            denormalizationContext: ['groups' => 'product_option_value:patch'],
            processor: PersistProcessor::class,
        ),
    ]
)]
class ProductOptionValue implements RessourceInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:get', 'product_option_value', 'product_option_value:get'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:get', 'product_option_value', 'product_option_value:get', 'product_option_value:patch'])]
    private ?string $value = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 17, scale: 2)]
    #[Groups(['product:get', 'product_option_value', 'product_option_value:get', 'product_option_value:patch'])]
    private ?string $priceAdjustment = null;

    #[ORM\ManyToOne(inversedBy: 'productOptionValues')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['product_option_value', 'product_option_value:get'])]
    private ?ProductOption $options = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getPriceAdjustment(): ?string
    {
        return $this->priceAdjustment;
    }

    public function setPriceAdjustment(string $priceAdjustment): static
    {
        $this->priceAdjustment = $priceAdjustment;

        return $this;
    }

    public function getOptions(): ?ProductOption
    {
        return $this->options;
    }

    public function setOptions(?ProductOption $options): static
    {
        $this->options = $options;

        return $this;
    }
}
