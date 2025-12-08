<?php

namespace App\Repository;

use App\Entity\Equipe;
use App\Entity\EquipeSaison;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementEquipeSaison;
use App\Traitement\Controlleur\ControlleurEquipeSaison;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<EquipeSaison>
 */
class EquipeSaisonRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipeSaison::class);
    }

    public function new(): EquipeSaison
    {
        return new EquipeSaison;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementEquipeSaison(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurEquipeSaison; 
        $this->setControlleur(controlleur: $objet);
    }

    public function getListeEquipes(int $id_championnat) : array
    {
        $this->setRepository(repository: new EquipeRepository(registry: $this->registry));
        return $this->getRepository()->findOptionsById(id: $id_championnat);
    }

    public function findSelect(int $id) : Equipe
    {
        $this->setRepository(repository: new EquipeRepository(registry: $this->registry));
        return $this->getRepository()->findOneBy(criteria: ['id' => $id]);
    }

    public function saisonEquipeExiste(int $id_saison, int $id_equipe) : int
    {
        $objet = $this->findOneBy(criteria: ['saison' => $id_saison, 'equipe' => $id_equipe]);
        return $objet ? $objet->getId() : 0;
    }

}
