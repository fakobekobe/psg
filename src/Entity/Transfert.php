<?php

namespace App\Entity;

use App\Repository\TransfertRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TransfertRepository::class)]
#[UniqueEntity(fields:['equipeSaison','joueur'], errorPath: 'joueur', message:"Ce joueur est déjà transféré dans ce club.")]
class Transfert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?EquipeSaison $equipeSaison = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Joueur $joueur = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getJoueur(): ?Joueur
    {
        return $this->joueur;
    }

    public function setJoueur(?Joueur $joueur): static
    {
        $this->joueur = $joueur;

        return $this;
    }
}
