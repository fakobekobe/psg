<?php

namespace App\Repository;

use App\Entity\Preponderance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementPreponderance;
use App\Traitement\Controlleur\ControlleurPreponderance;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<Preponderance>
 */
class PreponderanceRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Preponderance::class);
    }

    public function new(): Preponderance
    {
        return new Preponderance;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementPreponderance(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurPreponderance; 
        $this->setControlleur(controlleur: $objet);
    }
}
