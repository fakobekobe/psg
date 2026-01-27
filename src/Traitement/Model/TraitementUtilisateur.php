<?php

namespace App\Traitement\Model;

use App\Entity\Utilisateur;
use App\Traitement\Abstrait\TraitementAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
        $objet['email'] = ($donnees[0])->getEmail();
        $objet['actif'] = ($donnees[0])->isActif();
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
            $email = htmlspecialchars(string: $data->getEmail());
            $actif = $data->isActif() ? 'Oui' : 'Non';

            $i++;
            $v = ($i != $nb) ? '!x!':'';
            $tab .=  $i . $separateur . 
            $nom . $separateur . 
            $email . $separateur . 
            $actif . $separateur . 
            $this->lien_a($data->getId(), $email) . $v;
        }

        return $tab;
    }

    public function actionAjouterSucces(mixed ...$donnees): JsonResponse
    { 
        $form = $this->form->getData();

        $objet = $donnees['donnees'][1];
        $objet->setNom($form['nom']);
        $objet->setEmail($form['email']);
        $objet->setActif($form['actif'] ?? false);
        $objet->setPassword(password: $this->userPasswordHasher->hashPassword(user: $objet, plainPassword: $objet->getPassword()));
        $this->em->persist(object: $objet);
        
        try{
            $this->em->flush();
        }catch(UniqueConstraintViolationException $e)
        {
            return new JsonResponse(data: [
                'code' => self::EXCEPTION,
                'exception' => "Cette adresse email existe déjà."
            ]);
        }

        return new JsonResponse(data: [
            'code' => self::SUCCES,
        ]);
    }

    public function actionAjouterEchec(mixed $erreurs): JsonResponse
    {
        if (!$erreurs)
            $erreurs = ['plainPassword_first' => ['0' => "Mot de passe invalide (12 caractères minimum)"]];
        return new JsonResponse(data: [
            'code' => self::ECHEC,
            'erreurs' => $erreurs,
        ]);
    }

    public function actionModifier(mixed ...$donnees) : JsonResponse
    {        
        if($donnees[1])
        {
            return $this->actionModifierSucces(objet: $donnees[2]?? null);
        }else{                    
            $erreurs = Utilitaire::getErreur(form: $this->form);
            return $this->actionAjouterEchec(erreurs: $erreurs);
        }
    }

    public function actionModifierSucces(mixed $objet = null): JsonResponse
    {
        $form = $this->form->getData();
        $objet->setNom($form['nom']);
        $objet->setEmail($form['email']);
        $objet->setActif($form['actif'] ?? false);
        $objet->setPassword(password: $this->userPasswordHasher->hashPassword(user: $objet, plainPassword: $objet->getPassword()));
        
        try{
            $this->em->flush();
        }catch(UniqueConstraintViolationException $e)
        {
            return new JsonResponse(data: [
                'code' => self::EXCEPTION,
                'exception' => "Cette adresse email existe déjà."
            ]);
        }

        return new JsonResponse(data: ['code' => self::SUCCES]);
    }    
}