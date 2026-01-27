<?php

namespace App\Repository;

use App\Entity\Droit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementDroit;
use App\Traitement\Controlleur\ControlleurDroit;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<Droit>
 */
class DroitRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Droit::class);
    }

    public function new(): Droit
    {
        return new Droit;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementDroit(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurDroit; 
        $this->setControlleur(controlleur: $objet);
    }
}
