<?php

namespace App\Entity;

use App\Repository\StatistiqueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: StatistiqueRepository::class)]
#[UniqueEntity(fields:['matchDispute','periode'], errorPath: 'matchDispute', message:"Ce match possède déjà des statistiques dans cette période.")]
class Statistique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $score = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?float $possession = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $totalTir = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $tirCadre = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $grosseChance = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $corner = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $cartonJaune = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $cartonRouge = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $horsJeu = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $coupsFranc = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $touche = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $faute = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $tacle = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $arret = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?MatchDispute $matchDispute = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Periode $periode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getPossession(): ?float
    {
        return $this->possession;
    }

    public function setPossession(float $possession): static
    {
        $this->possession = $possession;

        return $this;
    }

    public function getTotalTir(): ?int
    {
        return $this->totalTir;
    }

    public function setTotalTir(int $totalTir): static
    {
        $this->totalTir = $totalTir;

        return $this;
    }

    public function getTirCadre(): ?int
    {
        return $this->tirCadre;
    }

    public function setTirCadre(int $tirCadre): static
    {
        $this->tirCadre = $tirCadre;

        return $this;
    }

    public function getGrosseChance(): ?int
    {
        return $this->grosseChance;
    }

    public function setGrosseChance(int $grosseChance): static
    {
        $this->grosseChance = $grosseChance;

        return $this;
    }

    public function getCorner(): ?int
    {
        return $this->corner;
    }

    public function setCorner(int $corner): static
    {
        $this->corner = $corner;

        return $this;
    }

    public function getCartonJaune(): ?int
    {
        return $this->cartonJaune;
    }

    public function setCartonJaune(int $cartonJaune): static
    {
        $this->cartonJaune = $cartonJaune;

        return $this;
    }

    public function getCartonRouge(): ?int
    {
        return $this->cartonRouge;
    }

    public function setCartonRouge(int $cartonRouge): static
    {
        $this->cartonRouge = $cartonRouge;

        return $this;
    }

    public function getHorsJeu(): ?int
    {
        return $this->horsJeu;
    }

    public function setHorsJeu(int $horsJeu): static
    {
        $this->horsJeu = $horsJeu;

        return $this;
    }

    public function getCoupsFranc(): ?int
    {
        return $this->coupsFranc;
    }

    public function setCoupsFranc(int $coupsFranc): static
    {
        $this->coupsFranc = $coupsFranc;

        return $this;
    }

    public function getTouche(): ?int
    {
        return $this->touche;
    }

    public function setTouche(int $touche): static
    {
        $this->touche = $touche;

        return $this;
    }

    public function getFaute(): ?int
    {
        return $this->faute;
    }

    public function setFaute(int $faute): static
    {
        $this->faute = $faute;

        return $this;
    }

    public function getTacle(): ?int
    {
        return $this->tacle;
    }

    public function setTacle(int $tacle): static
    {
        $this->tacle = $tacle;

        return $this;
    }

    public function getArret(): ?int
    {
        return $this->arret;
    }

    public function setArret(int $arret): static
    {
        $this->arret = $arret;

        return $this;
    }

    public function getMatchDispute(): ?MatchDispute
    {
        return $this->matchDispute;
    }

    public function setMatchDispute(?MatchDispute $matchDispute): static
    {
        $this->matchDispute = $matchDispute;

        return $this;
    }

    public function getPeriode(): ?Periode
    {
        return $this->periode;
    }

    public function setPeriode(?Periode $periode): static
    {
        $this->periode = $periode;

        return $this;
    }
}
