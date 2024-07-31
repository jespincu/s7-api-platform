<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use App\Repository\ManufacturerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ManufacturerRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(), 
        new Put(), 
        new Patch()]
)]
class Manufacturer
{
    /** The ID of the Manufacturer */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** The name of the Manufacturer */
    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Groups(['product.read'])]
    private ?string $name = '';

    /** The description of the Manufacturer */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = '';

    /** The country code of the Manufacturer */
    #[ORM\Column(length: 3)]
    #[Assert\NotBlank]
    private ?string $countryCode = '';

    /** The date that the Manufacturer was listed */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $listedDate = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'manufacturer', cascade: ['persist', 'remove'])]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getListedDate(): ?\DateTimeInterface
    {
        return $this->listedDate;
    }

    public function setListedDate(\DateTimeInterface $listedDate)
    {
        $this->listedDate = $listedDate;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setManufacturer($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getManufacturer() === $this) {
                $product->setManufacturer(null);
            }
        }

        return $this;
    }
}
