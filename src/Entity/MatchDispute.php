<?php

namespace App\Entity;

use App\Repository\MatchDisputeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: MatchDisputeRepository::class)]
#[UniqueEntity(fields:['rencontre','preponderance','equipeSaison'], errorPath: 'equipeSaison', message:"Ce match disputé existe déjà.")]
class MatchDispute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Rencontre $rencontre = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Preponderance $preponderance = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?EquipeSaison $equipeSaison = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRencontre(): ?Rencontre
    {
        return $this->rencontre;
    }

    public function setRencontre(?Rencontre $rencontre): static
    {
        $this->rencontre = $rencontre;

        return $this;
    }

    public function getPreponderance(): ?Preponderance
    {
        return $this->preponderance;
    }

    public function setPreponderance(?Preponderance $preponderance): static
    {
        $this->preponderance = $preponderance;

        return $this;
    }

    public function getEquipeSaison(): ?EquipeSaison
    {
        return $this->equipeSaison;
    }

    public function setEquipeSaison(?EquipeSaison $equipeSaison): static
    {
        $this->equipeSaison = $equipeSaison;

        return $this;
    }
}
