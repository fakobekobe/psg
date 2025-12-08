<?php

namespace App\Repository;

use App\Entity\Entraineur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementEntraineur;
use App\Traitement\Controlleur\ControlleurEntraineur;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<Entraineur>
 */
class EntraineurRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Entraineur::class);
    }

    public function new(): Entraineur
    {
        return new Entraineur;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementEntraineur(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurEntraineur; 
        $this->setControlleur(controlleur: $objet);
    }
}
