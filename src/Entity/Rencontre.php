<?php

namespace App\Entity;

use App\Repository\RencontreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use \DateTime;

#[ORM\Entity(repositoryClass: RencontreRepository::class)]
class Rencontre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?DateTime $dateHeureRencontre = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $temperature = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Calendrier $calendrier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateHeureRencontre(): ?DateTime
    {
        return $this->dateHeureRencontre;
    }

    public function setDateHeureRencontre(?DateTime $dateHeureRencontre): static
    {
        $this->dateHeureRencontre = $dateHeureRencontre;

        return $this;
    }

    public function getDateHeureRencontreHTML(): string
    {
        return $this->dateHeureRencontre->format(format:"Y-m-d\\TH:i:s");
    }

    public function getDate(): string
    {
        return $this->dateHeureRencontre->format(format:"d/m/Y");
    }

    public function getHeure(): string
    {
        return $this->dateHeureRencontre->format(format:"H:i:s");
    }

    public function getTemperature(): ?int
    {
        return $this->temperature;
    }

    public function getTemperatureAfficher(): string
    {
        return $this->temperature . '°C';
    }

    public function setTemperature(?int $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getCalendrier(): ?Calendrier
    {
        return $this->calendrier;
    }

    public function setCalendrier(?Calendrier $calendrier): static
    {
        $this->calendrier = $calendrier;

        return $this;
    }
}
