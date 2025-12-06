<?php

namespace App\Repository;

use App\Entity\Joueur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementJoueur;
use App\Traitement\Controlleur\ControlleurJoueur;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<Joueur>
 */
class JoueurRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Joueur::class);
    }

    public function new(): Joueur
    {
        return new Joueur;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementJoueur(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurJoueur; 
        $this->setControlleur(controlleur: $objet);
    }
}
