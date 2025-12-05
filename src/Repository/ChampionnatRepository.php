<?php

namespace App\Repository;

use App\Entity\Championnat;
use App\Trait\TraitementTrait;
use App\Traitement\Controlleur\ControlleurChampionnat;
use App\Traitement\Model\TraitementChampionnat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<Championnat>
 */
class ChampionnatRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {        
        parent::__construct($registry, Championnat::class);
    }

    public function new(): Championnat
    {
        return new Championnat;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementChampionnat(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurChampionnat; 
        $this->setControlleur(controlleur: $objet);
    }
    
}
