<?php

namespace App\Entity;

use App\Repository\JourneeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: JourneeRepository::class)]
#[UniqueEntity(fields:"numero")]
class Journee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?int $numero = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->numero == 1 ? $this->numero . 'ère journée' : $this->numero . 'e journée';
    }

    public function getDescriptionSimple(): string
    {
        return $this->numero == 1 ? $this->numero . 'ère' : $this->numero . 'e';
    }
}
