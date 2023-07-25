<?php

namespace App\Entity;

use App\Repository\MonumentHistoriqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MonumentHistoriqueRepository::class)]
class MonumentHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Endroit $idEndroit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdEndroit(): ?Endroit
    {
        return $this->idEndroit;
    }

    public function setIdEndroit(?Endroit $idEndroit): static
    {
        $this->idEndroit = $idEndroit;

        return $this;
    }
}
