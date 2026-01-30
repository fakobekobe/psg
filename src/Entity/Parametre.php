<?php

namespace App\Entity;

use App\Repository\ParametreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParametreRepository::class)]
class Parametre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $domicile = null;

    #[ORM\Column(nullable: true)]
    private ?int $exterieur = null;

    #[ORM\Column(nullable: true)]
    private ?int $premiereMT = null;

    #[ORM\Column(nullable: true)]
    private ?int $secondeMT = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomicile(): ?int
    {
        return $this->domicile;
    }

    public function setDomicile(?int $domicile): static
    {
        $this->domicile = $domicile;

        return $this;
    }

    public function getExterieur(): ?int
    {
        return $this->exterieur;
    }

    public function setExterieur(?int $exterieur): static
    {
        $this->exterieur = $exterieur;

        return $this;
    }

    public function getPremiereMT(): ?int
    {
        return $this->premiereMT;
    }

    public function setPremiereMT(?int $premiereMT): static
    {
        $this->premiereMT = $premiereMT;

        return $this;
    }

    public function getSecondeMT(): ?int
    {
        return $this->secondeMT;
    }

    public function setSecondeMT(?int $secondeMT): static
    {
        $this->secondeMT = $secondeMT;

        return $this;
    }
}
