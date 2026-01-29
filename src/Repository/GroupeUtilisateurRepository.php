<?php

namespace App\Repository;

use App\Entity\Groupe;
use App\Entity\GroupeUtilisateur;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementGroupeUtilisateur;
use App\Traitement\Controlleur\ControlleurGroupeUtilisateur;
use Symfony\Component\Form\FormInterface;

/**
 * @extends ServiceEntityRepository<GroupeUtilisateur>
 */
class GroupeUtilisateurRepository extends ServiceEntityRepository
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupeUtilisateur::class);
    }

    public function new(): GroupeUtilisateur
    {
        return new GroupeUtilisateur;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null): void
    {
        $objet = new TraitementGroupeUtilisateur(em: $em, form: $form, repository: $repository); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurGroupeUtilisateur; 
        $this->setControlleur(controlleur: $objet);
    }

    public function existe(Groupe $groupe, Utilisateur $utilisateur) : bool
    {
        return $this->findOneBy(criteria: ['groupe' => $groupe, 'utilisateur' => $utilisateur]) ? true : false;
    }

    public function utilisateur(int $id_utilisateur) : ?Utilisateur
    {
        $this->setRepository(repository: new UtilisateurRepository(registry: $this->registry));
        return $this->getRepository()->findOneBy(criteria: ['id' => $id_utilisateur]);
    }

    public function groupe(int $id_groupe) : ?Groupe
    {
        $this->setRepository(repository: new GroupeRepository(registry: $this->registry));
        return $this->getRepository()->findOneBy(criteria: ['id' => $id_groupe]);
    }

    public function utilisateurs() : array
    {
        $this->setRepository(repository: new UtilisateurRepository(registry: $this->registry));
        return $this->getRepository()->findBy(criteria: [], orderBy: ['id' => 'DESC']);
    }

    public function groupes() : array
    {
        $this->setRepository(repository: new GroupeRepository(registry: $this->registry));
        return $this->getRepository()->findBy(criteria: [], orderBy: ['id' => 'DESC']);
    }

    /**
     * getGroupeUtilisateur permet de retourner un tableau contenu le nombre d'utilisateur groupé par groupe
     * @return array
     */
    public function getGroupeUtilisateur() : array
    {
        return $this->createQueryBuilder(alias: 'a')
            ->select('g.id, g.nom as groupe, COUNT(a.utilisateur) as utilisateurs')
            ->leftJoin(join: 'a.groupe', alias: 'g')
            ->groupBy('g.nom')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * getUtilisateursByGroupe est une fonction qui retoune un tableau d'utilisateurs selon l'id_groupe
     * @param int $id_groupe Le groupe à rechercher
     * @return array La liste des utilisateurs ou un tableau vide.
     */
    public function getUtilisateursByGroupe(int $id_groupe) : array
    {
        $liste = $this->createQueryBuilder(alias: 'a')
            ->select('a')
            ->where('a.groupe = :id_groupe')
            ->setParameter(key: 'id_groupe', value: $id_groupe)
            ->getQuery()
            ->getResult()
            ;
        
        if($liste)
        {
            $utilisateurs = [];
            foreach($liste as $objet)
            {
               $utilisateurs[] = $objet->getUtilisateur(); 
            }

            $liste = $utilisateurs;
        }

        return $liste;
    }

    /**
     * getGroupesByUtilisateur est une fonction qui retoune la liste des groupes selon l'id_utilisateur
     * @param int $id_groupe L'utilisateur à rechercher
     * @return array La liste des groupes ou un tableau vide.
     */
    public function getGroupesByUtilisateur(int $id_utilisateur) : array
    {
        $liste = $this->createQueryBuilder(alias: 'a')
            ->select('a')
            ->where('a.utilisateur = :id_utilisateur')
            ->setParameter(key: 'id_utilisateur', value: $id_utilisateur)
            ->getQuery()
            ->getResult()
            ;
        
        if($liste)
        {
            $groupes = [];
            foreach($liste as $objet)
            {
               $groupes[] = $objet->getGroupe()->getNom(); 
            }

            $liste = $groupes;
        }

        return $liste;
    }

    /**
     * @see DroitGroupePageRepository::getDroitsPagesByGroupe
     */
    public function getDroitsPagesByGroupe(int $id_groupe, ?int $id_droit = null) : array
    {
        $this->setRepository(repository: new DroitGroupePageRepository(registry: $this->registry));
        return $this->getRepository()->getDroitsPagesByGroupe(id_groupe: $id_groupe, id_droit: $id_droit);
    }

}
