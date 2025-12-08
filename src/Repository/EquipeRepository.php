<?php

namespace App\Repository;

use App\Entity\Equipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Trait\TraitementTraitSelect;
use App\Traitement\Model\TraitementEquipe;
use App\Traitement\Controlleur\ControlleurEquipe;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<Equipe>
 */
class EquipeRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    use TraitementTraitSelect;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipe::class);
    }

    public function new(): Equipe
    {
        return new Equipe;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementEquipe(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurEquipe; 
        $this->setControlleur(controlleur: $objet);
    }

    public function findOptionsById(int $id) : array
    {
        return $this->createQueryBuilder(alias: 'x')
            ->select(['x.id', 'x.nom as libelle'])            
            ->andWhere('x.championnat = :id')
            ->setParameter( 'id',  $id)
            ->orderBy(sort: 'x.championnat', order: 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
