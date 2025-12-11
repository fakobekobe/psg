<?php

namespace App\Repository;

use App\Entity\EquipeSaison;
use App\Entity\MatchDispute;
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

    public function getListeRencontres(int $id_calendrier) : array
    {
        $this->setRepository(repository: new RencontreRepository(registry: $this->registry));
        return $this->getRepository()->findBy(criteria: ['calendrier' => $id_calendrier]);
    }
}
