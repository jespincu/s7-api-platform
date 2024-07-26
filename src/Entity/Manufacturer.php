<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ManufacturerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ManufacturerRepository::class)]
#[ApiResource]
class Manufacturer
{
    /** The ID of the Manufacturer */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** The name of the Manufacturer */
    #[ORM\Column(length: 150)]
    private ?string $name = null;

    /** The description of the Manufacturer */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /** The country code of the Manufacturer */
    #[ORM\Column(length: 3)]
    private ?string $countryCode = null;

    /** The date that the Manufacturer was listed */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $listedDate = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getListedDate(): ?\DateTimeInterface
    {
        return $this->listedDate;
    }

    public function setListedDate(\DateTimeInterface $listedDate): static
    {
        $this->listedDate = $listedDate;

        return $this;
    }
}
