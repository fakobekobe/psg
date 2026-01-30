<?php

namespace App\Repository;

use App\Entity\Parametre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementParametre;
use App\Traitement\Controlleur\ControlleurParametre;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<Parametre>
 */
class ParametreRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Parametre::class);
    }

    public function new(): Parametre
    {
        // On retourne la configuration en cours ou on crée une nouvelle
        return $this->getParametre() ?: new Parametre;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementParametre(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurParametre; 
        $this->setControlleur(controlleur: $objet);
    }

    /**
     * getParametre permet de retourner le parametre en cours
     * @return object|null
     */
    public function getParametre() : ?Parametre
    {
        return $this->findOneBy(criteria: ['id' => 1]);
    }

    public function getDomicile() : int
    {
        $parametre = $this->getParametre();
        return $parametre ? $parametre->getDomicile() : 1;
    }

    public function getExterieur() : int
    {
        $parametre = $this->getParametre();
        return $parametre ? $parametre->getExterieur() : 1;
    }

    public function getPremiereMT() : int
    {
        $parametre = $this->getParametre();
        return $parametre ? $parametre->getPremiereMT() : 1;
    }

    public function getSecondeMT() : int
    {
        $parametre = $this->getParametre();
        return $parametre ? $parametre->getSecondeMT() : 1;
    }
}
