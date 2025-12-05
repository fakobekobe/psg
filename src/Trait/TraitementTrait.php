<?php

namespace App\Trait;

use App\Traitement\Interface\ControlleurInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Traitement\Interface\TraitementInterface;


trait TraitementTrait
{
    protected ServiceEntityRepository $objetRepo;
    protected TraitementInterface $objetTraitement;
    protected ControlleurInterface $objetControlleur;

    public function setRepository(ServiceEntityRepository $repository): void
    {
        $this->objetRepo = $repository;
    }

    public function getRepository() : ServiceEntityRepository
    {
        return $this->objetRepo;
    }

    public function setTraitement(TraitementInterface $traitement): void
    {
        $this->objetTraitement = $traitement;
    }

    public function getTraitement() : TraitementInterface
    {
        return $this->objetTraitement;
    }

    public function setControlleur(ControlleurInterface $controlleur): void
    {
        $this->objetControlleur = $controlleur;
    }

    public function getControlleur() : ControlleurInterface
    {
        return $this->objetControlleur;
    }
}