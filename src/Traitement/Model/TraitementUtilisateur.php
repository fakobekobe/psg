<?php

namespace App\Traitement\Model;

use App\Entity\Utilisateur;
use App\Traitement\Abstrait\TraitementAbstrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TraitementUtilisateur extends TraitementAbstrait
{
    public function __construct(
        protected ?EntityManagerInterface $em = null,
        protected ?FormInterface $form = null,
        protected ?ServiceEntityRepository $repository = null,
        protected ?UserPasswordHasherInterface $userPasswordHasher = null        
        )
    {
        parent::__construct(em: $this->em, form: $this->form, repository: $this->repository);
    }

    protected function getObjet(mixed ...$donnees): array
    {
        $objet['id'] = ($donnees[0])->getId();
        $objet['nom'] = ($donnees[0])->getNom();
        return $objet;
    }

    protected function chaine_data(mixed ...$donnees): string
    {        
        $tab = "";
        $i = 0;
        $separateur = ';x;';
        $nb = count(value: $donnees[0]); // $donnees[0] contient la liste des objets

        foreach($donnees[0] as $data)
        {
            $nom = ucfirst(string: htmlspecialchars(string: $data->getNom()));

            $i++;
            $v = ($i != $nb) ? '!x!':'';
            $tab .=  $i . $separateur . 
            $nom . $separateur . 
            $this->lien_a($data->getId(), $nom) . $v;
        }

        return $tab;
    }

    public function traitementAjouterSucces(mixed $objet): JsonResponse
    {
        /**
         * @var Utilisateur
         */
        $objet = $this->form->getData();
        $objet->setPassword(password: $this->userPasswordHasher->hashPassword(user: $objet, plainPassword: $this->form->get(name: 'plainPassword')->getData()));
        $this->em->persist(object: $objet);
        $this->em->flush();
        return new JsonResponse(data: [
            'code' => self::SUCCES,
        ]);
    }

    public function traitementAjouterEchec(mixed $erreurs): JsonResponse
    {
        if (!$erreurs)
            $erreurs = ['plainPassword_first' => ['0' => "Mot de passe invalide"]];
        return new JsonResponse(data: [
            'code' => self::ECHEC,
            'erreurs' => $erreurs,
        ]);
    }
}