<?php

namespace App\Entity;

use App\Repository\RencontreRepository;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use \DateTime;
use IntlDateFormatter;

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
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Calendrier $calendrier = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Saison $saison = null;

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
        return $this->dateHeureRencontre->format(format:"H:i");
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

    public function getSaison(): ?Saison
    {
        return $this->saison;
    }

    public function setSaison(?Saison $saison): static
    {
        $this->saison = $saison;

        return $this;
    }

    public function getDescription() : string
    {
        $fmt = datefmt_create(
            locale: 'fr_FR',
            dateType: IntlDateFormatter::FULL,
            timeType: IntlDateFormatter::FULL,
            timezone: 'Africa/Abidjan',
            calendar: IntlDateFormatter::GREGORIAN,
            pattern: "EEEE, dd MMMM Y à HH:mm"
        );

        return ucfirst(string: datefmt_format(formatter: $fmt, datetime: $this->dateHeureRencontre));
    }
}
