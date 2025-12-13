<?php

namespace App\Repository;

use App\Entity\Statistique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Controlleur\ControlleurMatchDispute;
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

    public function findStatistiqueByMatchByPeriode(int $id_match, int $id_periode) : int
    {
        $retour = $this->createQueryBuilder(alias: 'x')
            ->select(['x.id'])        
            ->andWhere('x.matchDispute = :id_match AND x.periode = :id_periode')
            ->setParameters(parameters: new ArrayCollection(elements: [
                new Parameter(name: 'id_match', value: $id_match),
                new Parameter(name: 'id_periode', value: $id_periode),
            ]))
            ->getQuery()
            ->getResult()
        ;

        return $retour ? $retour[0]['id'] : 0;
    }
}
