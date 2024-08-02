<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[
    ApiResource(
        normalizationContext: ['groups' => ['product.read']],
        denormalizationContext: ['groups' => ['product.write']],
        operations: [
            new GetCollection(
                paginationEnabled: true,
                paginationItemsPerPage: 5,
                paginationMaximumItemsPerPage: 10,
                paginationClientEnabled: true,
                paginationClientItemsPerPage: true,
                security: 'is_granted("ROLE_ADMIN")'
            ),
            new Get(security: 'is_granted("ROLE_ADMIN")'),
            new Post(security: 'is_granted("ROLE_ADMIN")'), 
            new Put(security: 'is_granted("ROLE_USER") and object.getOwner() == user',
                    securityMessage: 'A Product can only be updated by the owner'), 
            new Patch(security: 'is_granted("ROLE_ADMIN")'),
            new Delete(security: 'is_granted("ROLE_ADMIN")')
        ]
    ),
    ApiFilter(
        SearchFilter::class,
        properties: [
            'name' => SearchFilter::STRATEGY_PARTIAL,        
            'description' => SearchFilter::STRATEGY_PARTIAL,       
            'manufacturer.countryCode' => SearchFilter::STRATEGY_EXACT,       
            'manufacturer.id' => SearchFilter::STRATEGY_EXACT       
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
    #[Groups(['product.read','product.write'])]
    private ?string $mpn = null;

    /** The name of the Product */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['product.read','product.write'])]
    private ?string $name = '';

    /** The description of the Product */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['product.read','product.write'])]
    private ?string $description = '';

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    #[Groups(['product.read','product.write'])]
    private ?\DateTimeInterface $issueDate = null;

    /** The Manufacturer of the Product */
    #[ORM\ManyToOne(targetEntity: Manufacturer::class, inversedBy: 'products')]
    #[Assert\NotNull]
    #[Groups(['product.read','product.write'])]
    private ?Manufacturer $manufacturer = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['product.read','product.write'])]
    private ?User $owner = null;

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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
