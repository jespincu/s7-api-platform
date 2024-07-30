<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[
    ApiResource,
    ApiFilter(
        SearchFilter::class,
        properties: [
            'name' => SearchFilter::STRATEGY_PARTIAL,        
            'description' => SearchFilter::STRATEGY_PARTIAL,       
            'manufacturer.countryCode' => SearchFilter::STRATEGY_EXACT       
        ]
    ),
    ApiFilter(
        OrderFilter::class,
        properties: [
            'issueDate'     
        ]
    )
        
]
class Product
{
    /** The ID of the Product */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** The MPN(manufacturer part number) of the Product */
    #[ORM\Column(length: 255)]
    #[Assert\NotNull]
    private ?string $mpn = null;

    /** The name of the Product */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = '';

    /** The description of the Product */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = '';

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $issueDate = null;

    /** The Manufacturer of the Product */
    #[ORM\ManyToOne(targetEntity: Manufacturer::class, inversedBy: 'products')]
    private ?Manufacturer $manufacturer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMpn(): ?string
    {
        return $this->mpn;
    }

    public function setMpn(?string $mpn): static
    {
        $this->mpn = $mpn;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getIssueDate(): ?\DateTimeInterface
    {
        return $this->issueDate;
    }

    public function setIssueDate(?\DateTimeInterface $issueDate): static
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }
}
