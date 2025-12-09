<?php

namespace App\Repository;

use App\Entity\Calendrier;
use App\Entity\Rencontre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementRencontre;
use App\Traitement\Controlleur\ControlleurRencontre;
use Symfony\Component\Form\FormInterface;
/**
 * @extends ServiceEntityRepository<Rencontre>
 */
class RencontreRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Rencontre::class);
    }

    public function new(): Rencontre
    {
        return new Rencontre;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementRencontre(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurRencontre; 
        $this->setControlleur(controlleur: $objet);
    }

    public function getListeJournees(int $id_championnat) : array
    {
        $this->setRepository(repository: new CalendrierRepository(registry: $this->registry));
        return $this->getRepository()->findOptionsById(id: $id_championnat);
    }

    public function findSelect(int $id) : ?Calendrier
    {
        $this->setRepository(repository: new CalendrierRepository(registry: $this->registry));
        return $this->getRepository()->findOneBy(criteria: ['id' => $id]);
    }
}
