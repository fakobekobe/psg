<?php

namespace App\Repository;

use App\Entity\Periode;
use App\Entity\Statistique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\EntityManagerInterface;
use App\Traitement\Model\TraitementStatistique;
use App\Trait\TraitementTrait;
use App\Traitement\Controlleur\ControlleurStatistique;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<Statistique>
 */
class StatistiqueRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Statistique::class);
    }

    public function new(): Statistique
    {
        return new Statistique;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementStatistique(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurStatistique; 
        $this->setControlleur(controlleur: $objet);
    }

    public function findStatistiqueByMatchByPeriode(int $id_match, int $id_periode) : ?Statistique
    {
        $retour = $this->createQueryBuilder(alias: 'x')
            ->andWhere('x.matchDispute = :id_match AND x.periode = :id_periode')
            ->setParameters(parameters: new ArrayCollection(elements: [
                new Parameter(name: 'id_match', value: $id_match),
                new Parameter(name: 'id_periode', value: $id_periode),
            ]))
            ->getQuery()
            ->getResult()            
        ;

        return $retour ? $retour[0] : null;
    }

    public function periode(int $id_periode): ?Periode
    {
        $this->setRepository(repository: new PeriodeRepository(registry: $this->registry));
        return $this->getRepository()->findOneBy(criteria: ['id' => $id_periode]);
    }

    public function periodes(): array
    {
        $this->setRepository(repository: new PeriodeRepository(registry: $this->registry));
        return $this->getRepository()->findAll();
    }

    public function getListeMatchByRencontre(int $id_rencontre): array
    {
        $this->setRepository(repository: new MatchDisputeRepository(registry: $this->registry));
        return $this->getRepository()->findBy(criteria: ['rencontre' => $id_rencontre]);
    }

    public function findStatistiqueByRencontreByPeriode(int $id_rencontre, int $id_periode) : array
    {
        return $this->createQueryBuilder(alias: 'x')
            ->leftJoin('x.matchDispute', 'm')
            ->andWhere('m.rencontre = :id_rencontre AND x.periode = :id_periode')
            ->setParameters(parameters: new ArrayCollection(elements: [
                new Parameter(name: 'id_rencontre', value: $id_rencontre),
                new Parameter(name: 'id_periode', value: $id_periode),
            ]))
            ->getQuery()
            ->getResult()            
        ;
    }
}
