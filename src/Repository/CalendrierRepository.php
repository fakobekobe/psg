<?php

namespace App\Repository;

use App\Entity\Calendrier;
use App\Entity\Journee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Trait\TraitementTraitSelect;
use App\Traitement\Model\TraitementCalendrier;
use App\Traitement\Controlleur\ControlleurCalendrier;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<Calendrier>
 */
class CalendrierRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    use TraitementTraitSelect;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Calendrier::class);
    }

    public function new(): Calendrier
    {
        return new Calendrier;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementCalendrier(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurCalendrier; 
        $this->setControlleur(controlleur: $objet);
    }

    public function getJournee(int $id) : ?Journee
    {
        $this->setRepository(repository: new JourneeRepository(registry: $this->registry));
        return $this->getRepository()->findOneBy(criteria: ['id' => $id]);
    }

    public function calendrierExiste(int $id_championnat, int $id_journee) : int
    {
        $calendrier = $this->findOneBy(criteria: ['championnat' => $id_championnat, 'journee' => $id_journee]);
        return $calendrier ? $calendrier->getId() : 0;
    }

    public function getCalendrier(int $id_championnat, int $id_journee) : ?Calendrier
    {
        return $this->findOneBy(criteria: ['championnat' => $id_championnat, 'journee' => $id_journee]);
    }

    public function findOptionsById(int $id) : array
    {
        return $this->createQueryBuilder(alias: 'x')
            ->select(['x.id', 'j.numero as libelle'])  
            ->leftJoin('x.journee', 'j')          
            ->andWhere('x.championnat = :id')
            ->setParameter( 'id',  $id)
            ->orderBy(sort: 'x.journee', order: 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
