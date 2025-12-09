<?php

namespace App\Entity;

use App\Repository\CalendrierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CalendrierRepository::class)]
#[UniqueEntity(fields:['championnat','journee'], errorPath: 'journee', message:"Cette journée est déjà associée à ce championnat.")]

class Calendrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Championnat $championnat = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Journee $journee = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChampionnat(): ?Championnat
    {
        return $this->championnat;
    }

    public function setChampionnat(?Championnat $championnat): static
    {
        $this->championnat = $championnat;

        return $this;
    }

    public function getJournee(): ?Journee
    {
        return $this->journee;
    }

    public function setJournee(?Journee $journee): static
    {
        $this->journee = $journee;

        return $this;
    }    
}
