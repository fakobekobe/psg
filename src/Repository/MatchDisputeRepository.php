<?php

namespace App\Repository;

use App\Entity\Calendrier;
use App\Entity\EquipeSaison;
use App\Entity\MatchDispute;
use App\Entity\Periode;
use App\Entity\Preponderance;
use App\Entity\Rencontre;
use App\Entity\Statistique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementMatchDispute;
use App\Traitement\Controlleur\ControlleurMatchDispute;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<MatchDispute>
 */
class MatchDisputeRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchDispute::class);
    }

    public function new(): MatchDispute
    {
        return new MatchDispute;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementMatchDispute(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurMatchDispute; 
        $this->setControlleur(controlleur: $objet);
    }

    public function getListeEquipes(int $id_championnat) : array
    {
        $this->setRepository(repository: new EquipeRepository(registry: $this->registry));
        return $this->getRepository()->findOptionsById(id: $id_championnat);
    }

    public function getEquipeSaisonJoueur(int $id_equipeSaison, int $id_joueur) : int
    {
        $objet = $this->findOneBy(criteria: ['equipeSaison' => $id_equipeSaison, 'joueur' => $id_joueur]);
        return $objet ? $objet->getId() : 0;
    }

    public function getEquipeSaison(int $id_equipe, int $id_saison) : ?EquipeSaison
    {
        $this->setRepository(repository: new EquipeSaisonRepository(registry: $this->registry));
        return $this->getRepository()->findOneBy(criteria: ['equipe' => $id_equipe, 'saison' => $id_saison]);
    }

    public function findSaisonJoueur(int $id_saison, int $id_joueur) : int
    {
        $retour = $this->createQueryBuilder(alias: 'x')
            ->select(['x.id',])  
            ->leftJoin('x.equipeSaison', 'e')          
            ->andWhere('e.saison = :id_saison AND x.joueur = :id_joueur')
            ->setParameters(parameters: new ArrayCollection(elements: [
                new Parameter(name: 'id_saison', value: $id_saison),
                new Parameter(name: 'id_joueur', value: $id_joueur),
            ]))
            ->getQuery()
            ->getResult()
        ;

        return $retour ? $retour[0]['id'] : 0;
    }

    public function getListeRencontres(int $id_calendrier, int $id_saison) : array
    {
        $this->setRepository(repository: new RencontreRepository(registry: $this->registry));
        return $this->getRepository()->findBy(criteria: ['calendrier' => $id_calendrier, 'saison' => $id_saison]);
    }

    //--------------------------------------
    public function findMatchDisputesByCalendrier(int $id_calendrier, int $id_saison) : array
    {
        return $this->createQueryBuilder(alias: 'x')
            ->select(['r.id',])  
            ->leftJoin('x.rencontre', 'r')          
            ->andWhere('r.calendrier = :id_calendrier AND r.saison = :id_saison')
            ->setParameters(parameters: new ArrayCollection(elements: [
                new Parameter('id_calendrier', $id_calendrier),
                new Parameter('id_saison', $id_saison),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMatchDisputeBySaisonByChampionnat(int $id_saison, int $id_championnat) : array
    {
        return $this->createQueryBuilder(alias: 'x')
            ->leftJoin('x.equipeSaison', 'e')          
            ->leftJoin('x.rencontre', 'r')          
            ->leftJoin('r.calendrier', 'c')          
            ->andWhere('e.saison = :id_saison AND c.championnat = :id_championnat')
            ->setParameters(parameters: new ArrayCollection(elements: [
                new Parameter(name: 'id_saison', value: $id_saison),
                new Parameter(name: 'id_championnat', value: $id_championnat),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }

    public function getCalendrier(int $id_calendrier) : ?Calendrier
    {
        $this->setRepository(repository: new CalendrierRepository(registry: $this->registry));
        return $this->getRepository()->findOneBy(criteria: ['id' => $id_calendrier]);
    }

    public function getRencontre(int $id_rencontre) : ?Rencontre
    {
        $this->setRepository(repository: new RencontreRepository(registry: $this->registry));
        return $this->getRepository()->findOneBy(criteria: ['id' => $id_rencontre]);
    }

    public function getClub(int $id_club) : ?EquipeSaison
    {
        $this->setRepository(repository: new EquipeSaisonRepository(registry: $this->registry));
        return $this->getRepository()->findOneBy(criteria: ['id' => $id_club]);
    }

    public function getPreponderance(int $id_preponderance) : ?Preponderance
    {
        $this->setRepository(repository: new PreponderanceRepository(registry: $this->registry));
        return $this->getRepository()->findOneBy(criteria: ['id' => $id_preponderance]);
    }

    public function findListeClubs(int $id_saison, int $id_championnat) : array
    {
        $this->setRepository(repository: new EquipeSaisonRepository(registry: $this->registry));
        return $this->getRepository()->findListeClubs(id_saison: $id_saison, id_championnat: $id_championnat);
    }

    public function findMatchByCalendrier(int $id_calendrier, int $id_saison) : array
    {
        return $this->createQueryBuilder(alias: 'x')
            ->leftJoin('x.rencontre', 'r')          
            ->andWhere('r.calendrier = :id_calendrier AND r.saison = :id_saison')
            ->setParameters(parameters: new ArrayCollection(elements: [
                new Parameter('id_calendrier', $id_calendrier),
                new Parameter('id_saison', $id_saison),
            ]))
            ->orderBy(sort: 'x.id', order: 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    //----------------------------------
    public function findMatchByCalendrierByCubByPreponderance(int $id_calendrier, int $id_saison, int $id_club, int $id_preponderance) : array
    {
        return $this->createQueryBuilder(alias: 'x')
            ->leftJoin('x.rencontre', 'r')          
            ->andWhere('r.calendrier = :id_calendrier AND r.saison = :id_saison AND x.equipeSaison = :id_club AND x.preponderance = :id_preponderance')
            ->setParameters(parameters: new ArrayCollection(elements: [
                new Parameter(name: 'id_calendrier', value: $id_calendrier),
                new Parameter(name: 'id_saison', value: $id_saison),
                new Parameter(name: 'id_club', value: $id_club),
                new Parameter(name: 'id_preponderance', value: $id_preponderance),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findStatistiqueByMatchByPeriode(int $id_match, int $id_periode) : ?Statistique
    {
        $this->setRepository(repository: new StatistiqueRepository(registry: $this->registry));
        return $this->getRepository()->findStatistiqueByMatchByPeriode(id_match: $id_match, id_periode: $id_periode);
    }

    public function statistique(): ?Statistique
    {
        $this->setRepository(repository: new StatistiqueRepository(registry: $this->registry));
        return $this->getRepository()->new();
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
}
