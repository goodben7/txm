<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use App\Model\RessourceInterface;
use App\Entity\ProductOptionValue;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ProductOptionRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;

#[ORM\Entity(repositoryClass: ProductOptionRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => 'product_option:get'], 
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
            denormalizationContext: ['groups' => 'product_option:patch'],
            processor: PersistProcessor::class,
        ),
    ]
)]
class ProductOption  implements RessourceInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:get', 'product_option', 'product_option:get'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:get', 'product_option', 'product_option:get', 'product_option:patch', 'order:get'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'productOptions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['product_option', 'product_option:get'])]
    private ?Product $product = null;

    /**
     * @var Collection<int, ProductOptionValue>
     */
    #[ORM\OneToMany(targetEntity: ProductOptionValue::class, mappedBy: 'options', orphanRemoval: true)]
    #[Groups(['product:get', 'product_option', 'product_option:get'])]
    private Collection $productOptionValues;

    public function __construct()
    {
        $this->productOptionValues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return Collection<int, ProductOptionValue>
     */
    public function getProductOptionValues(): Collection
    {
        return $this->productOptionValues;
    }

    public function addProductOptionValue(ProductOptionValue $productOptionValue): static
    {
        if (!$this->productOptionValues->contains($productOptionValue)) {
            $this->productOptionValues->add($productOptionValue);
            $productOptionValue->setOptions($this);
        }

        return $this;
    }

    public function removeProductOptionValue(ProductOptionValue $productOptionValue): static
    {
        if ($this->productOptionValues->removeElement($productOptionValue)) {
            // set the owning side to null (unless already changed)
            if ($productOptionValue->getOptions() === $this) {
                $productOptionValue->setOptions(null);
            }
        }

        return $this;
    }
}
