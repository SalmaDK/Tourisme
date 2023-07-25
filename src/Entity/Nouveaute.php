<?php

namespace App\Entity;

use App\Repository\NouveauteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NouveauteRepository::class)]
class Nouveaute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datePub = null;

    #[ORM\Column(length: 255)]
    private ?string $titreU = null;

    #[ORM\Column(length: 255)]
    private ?string $titreD = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getDatePub(): ?\DateTimeInterface
    {
        return $this->datePub;
    }

    public function setDatePub(\DateTimeInterface $datePub): static
    {
        $this->datePub = $datePub;

        return $this;
    }

    public function getTitreU(): ?string
    {
        return $this->titreU;
    }

    public function setTitreU(string $titreU): static
    {
        $this->titreU = $titreU;

        return $this;
    }

    public function getTitreD(): ?string
    {
        return $this->titreD;
    }

    public function setTitreD(string $titreD): static
    {
        $this->titreD = $titreD;

        return $this;
    }
}
