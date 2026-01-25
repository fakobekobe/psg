<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use App\Trait\TraitementTrait;
use App\Traitement\Model\TraitementUtilisateur;
use App\Traitement\Controlleur\ControlleurUtilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use TraitementTrait;
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function new(): Utilisateur
    {
        return new Utilisateur;
    }

    public function initialiserTraitement(
        ?EntityManagerInterface $em = null, 
        ?FormInterface $form = null, 
        ?ServiceEntityRepository $repository = null,
        ?UserPasswordHasherInterface $userPasswordHasher = null): void
    {
        $objet = new TraitementUtilisateur(em: $em, form: $form, repository: $repository, userPasswordHasher: $userPasswordHasher); 
        $this->setTraitement(traitement: $objet);
    }

    public function initialiserControlleur(): void  
    {
        $objet = new ControlleurUtilisateur; 
        $this->setControlleur(controlleur: $objet);
    }
}
