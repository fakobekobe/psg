<?php

namespace App\Repository;

use App\Entity\Droit;
use App\Entity\DroitGroupePage;
use App\Entity\Groupe;
use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementDroitGroupePage;
use App\Traitement\Controlleur\ControlleurDroitGroupePage;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;

/**
 * @extends ServiceEntityRepository<DroitGroupePage>
 */
class DroitGroupePageRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, DroitGroupePage::class);
    }

    public function new(): DroitGroupePage
    {
        return new DroitGroupePage;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementDroitGroupePage(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurDroitGroupePage; 
        $this->setControlleur(controlleur: $objet);
    }

    public function getAll() : array
    {
        return $this->findBy(criteria: [], orderBy: ['id' => 'DESC']);
    }

    //------------------------------
    public function groupe(int $id_groupe) : ?Groupe
    {
        $this->setRepository(repository: new GroupeRepository(registry: $this->registry));
        return $this->getRepository()->groupe(id_groupe: $id_groupe);
    }

    public function page(int $id_page) : ?Page
    {
        $this->setRepository(repository: new PageRepository(registry: $this->registry));
        return $this->getRepository()->page(id_page: $id_page);
    }

    public function droit(int $id_droit) : ?Droit
    {
        $this->setRepository(repository: new DroitRepository(registry: $this->registry));
        return $this->getRepository()->droit(id_droit: $id_droit);
    }

    //-------------------------------

    public function groupes() : array
    {
        $this->setRepository(repository: new GroupeRepository(registry: $this->registry));
        return $this->getRepository()->groupes();
    }

    public function pages() : array
    {
        $this->setRepository(repository: new PageRepository(registry: $this->registry));
        return $this->getRepository()->pages();
    }

    public function droits() : array
    {
        $this->setRepository(repository: new DroitRepository(registry: $this->registry));
        return $this->getRepository()->droits();
    }

    public function existe(Groupe $groupe, Droit $droit, Page $page) : bool
    {
        return $this->findOneBy(criteria: ['groupe' => $groupe, 'droit' => $droit, 'page' => $page]) ? true : false;
    }

    /**
     * getGroupeUtilisateur permet de retourner un tableau contenu le nombre d'utilisateur groupé par groupe
     * @return array
     */
    public function getDroitGroupePage() : array
    {
        return $this->createQueryBuilder(alias: 'a')
            ->select('a.id, g.nom as groupe, d.nom as droit, COUNT(a.page) as pages')
            ->leftJoin(join: 'a.groupe', alias: 'g')
            ->leftJoin(join: 'a.droit', alias: 'd')
            ->leftJoin(join: 'a.page', alias: 'p')
            ->groupBy('g.nom', 'd.nom')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * getDroitsPagesByGroupe est une fonction qui permet de récupérer un tableau contenant une liste de droit et page
     * selon l'id_ du groupe
     * @param int $id_groupe est la variable de l'id du groupe
     * @return array La liste des droit et page du groupe ou un tableau vide
     */
    public function getDroitsPagesByGroupe(int $id_groupe, ?int $id_droit = null) : array
    {
        // Les paramètres
        $requete = "a.groupe = :id_groupe";
        $parametres[] = new Parameter(name: 'id_groupe', value: $id_groupe);
        if($id_droit)
        {
            $requete .= " AND a.droit = :id_droit";
            $parametres[] = new Parameter(name: 'id_droit', value: $id_droit);
        }

        return $this->createQueryBuilder(alias: 'a')
            ->select('d.nom as droit, p.nom as page')
            ->leftJoin(join: 'a.droit', alias: 'd')
            ->leftJoin(join: 'a.page', alias: 'p')
            ->where($requete)
            ->setParameters(new ArrayCollection(elements: $parametres))
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @see GroupeUtilisateurRepository::getUtilisateursByGroupe()
     */
    public function getUtilisateursByGroupe(int $id_groupe) : array
    {
        $this->setRepository(repository: new GroupeUtilisateurRepository(registry: $this->registry));
        return $this->getRepository()->getUtilisateursByGroupe(id_groupe: $id_groupe);
    }
}
