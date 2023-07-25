<?php

namespace App\Entity;

use App\Repository\HotelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HotelRepository::class)]
class Hotel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkRes = null;

    #[ORM\Column]
    private ?int $nbretoile = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $equipement = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Endroit $idendroit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLinkRes(): ?string
    {
        return $this->linkRes;
    }

    public function setLinkRes(?string $linkRes): static
    {
        $this->linkRes = $linkRes;

        return $this;
    }

    public function getNbretoile(): ?int
    {
        return $this->nbretoile;
    }

    public function setNbretoile(int $nbretoile): static
    {
        $this->nbretoile = $nbretoile;

        return $this;
    }

    public function getEquipement(): ?string
    {
        return $this->equipement;
    }

    public function setEquipement(string $equipement): static
    {
        $this->equipement = $equipement;

        return $this;
    }

    public function getIdendroit(): ?Endroit
    {
        return $this->idendroit;
    }

    public function setIdendroit(?Endroit $idendroit): static
    {
        $this->idendroit = $idendroit;

        return $this;
    }
}
