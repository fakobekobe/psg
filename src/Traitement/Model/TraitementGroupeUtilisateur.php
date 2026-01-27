<?php

namespace App\Traitement\Model;

use App\Traitement\Abstrait\TraitementAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TraitementGroupeUtilisateur extends TraitementAbstrait
{
    public function __construct(
        protected ?EntityManagerInterface $em = null,
        protected ?FormInterface $form = null,
        protected ?ServiceEntityRepository $repository = null
        
        )
    {
        parent::__construct(em: $this->em, form: $this->form, repository: $this->repository);
    }

    protected function getObjet(mixed ...$donnees): array
    { 
        $objet['id'] = ($donnees[0])->getId();
        $objet['utilisateur'] = ($donnees[0])->getUtilisateur()->getNom();
        $objet['groupe'] = ($donnees[0])->getGroupe()->getNom();
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
            $id = $data['id'];
            $groupe = ucfirst(string: htmlspecialchars(string: $data['groupe']));
            $utilisateurs = $data['utilisateurs'];

            $i++;
            $v = ($i != $nb) ? '!x!':'';
            $tab .=  $i . $separateur . 
            $groupe . $separateur .
            $utilisateurs . $separateur .
            $this->lien_a($id, $groupe) . $v;
        }

        return $tab;
    }

    protected function actionAjouterSucces(mixed ...$donnees) : JsonResponse
    {
        $code = self::ECHEC;

        if (!empty($donnees['donnees'][1]['groupe']) and !empty($donnees['donnees'][1]['gerant'])) {
            foreach ($donnees['donnees'][1]['groupe'] as $id_groupe) {
                foreach ($donnees['donnees'][1]['gerant'] as $id_utilisateur) {
                    if (
                        $utilisateur = $this->repository->utilisateur(id_utilisateur: $id_utilisateur) and
                        $groupe = $this->repository->groupe(id_groupe: $id_groupe)
                    ) {
                        if (!$this->repository->existe(groupe: $groupe, utilisateur: $utilisateur)) {
                            $data = $this->repository->new();
                            $data->setUtilisateur($utilisateur);
                            $data->setGroupe($groupe);
                            $this->em->persist(object: $data);
                            // On ajoute les roles de l'utilisateurs
                            //Utilitaire::AjouteDroitUtilisateur(id_groupe: $id_groupe, objetRepo: $this->repository, utilisateurs: [$utilisateur]);
                            $this->em->flush();

                            $code = self::SUCCES;
                        }
                    }

                }
            }
        }

        return new JsonResponse(data: [
            'code' => $code,
            'data' => $donnees,
        ]);
    }

    public function actionSupprimer(mixed ...$donnees): JsonResponse
    {
        $id_groupe = $donnees[0] ?? $donnees['donnees'];
        $objet['data'] = $this->repository->findBy(criteria: ['groupe' => $id_groupe]);
        if ($objet['data']) {
            $objet['id_groupe'] = $id_groupe;
            return $this->actionSupprimerSucces(objet: $objet);
        } else {
            return $this->actionSupprimerEchec();
        }
    }

    protected function actionSupprimerSucces(mixed $objet): JsonResponse
    {
        $utilisateurs = [];
        // On récupère tous les utilisateurs
        foreach ($objet['data'] as $data) {
            $utilisateurs[] = $data->getUtilisateur();
        }

        // On Supprime tous les roles du groupe de la liste des utilisateurs du groupe
        //Utilitaire::SupprimeDroitUtilisateur(id_groupe: $objet['id_groupe'], objetRepo: $this->repository, utilisateurs: $utilisateurs);                  

        // On supprime tous les Utilisateurs du groupe dans GroupeUtilisateur
        foreach ($objet['data'] as $data) {
            $this->em->remove(object: $data);
        }

        $this->em->flush();

        return new JsonResponse(data: [
            'code' => self::SUCCES,
            'message' => "Votre données a bien été supprimée."
        ]);
    }

    public function actionModifier(mixed ...$donnees) : JsonResponse
    {        
        if($donnees[0]) 
        {                      
            return $this->actionModifierSucces(objet: $donnees[0]);                 

        }else{
            return $this->actionModifierEchec(erreurs: []);
        }        
    }

    public function actionModifierSucces(mixed $objet = null): JsonResponse
    {
        // On supprime tous les utilisateurs du groupe
        $this->actionSupprimer(donnees: $objet['id']);

        // On ajoute les utilisateurs dans le groupe
        $this->actionAjouterSucces(donnees: [null, $objet['data']]);

        return new JsonResponse(data: ['code' => self::SUCCES]);
    }

     public function actionModifierEchec(array $erreurs): JsonResponse
    {
        return new JsonResponse(data: [
            'code' => self::ECHEC,
            'erreurs' => 'Aucune donnée sélectionnée',
        ]);
    }

    public function actionFormulaire(mixed ...$donnees): JsonResponse
    {
        $utilisateurs = ($donnees[0])->utilisateurs();
        $groupes = ($donnees[0])->groupes();

        $reponse = [];

        if ($groupes) {
            $reponse['groupe'] = Utilitaire::chaineFormulaire(tableau: $groupes, class: 'cocher_groupe', nom: 'getNom', input_name: 'groupe', type: 'groupe', data: $donnees[1], objetRepository: $donnees[0]);
        } else {
            $reponse['groupe'] = '<h6>Aucun Groupe disponible</h6>';
        }

        if ($utilisateurs) {
            $reponse['utilisateur'] = Utilitaire::chaineFormulaire(tableau: $utilisateurs, class: 'cocher_gerant', nom: 'getNom', input_name: 'gerant', type: 'gerant', data: $donnees[1], objetRepository: $donnees[0]);
        } else {
            $reponse['utilisateur'] = '<h6>Aucun Utilisateur disponible</h6>';
        }

        return new JsonResponse(data: [
            'code' => static::SUCCES,
            'html' => $reponse
        ]);
    }

    public function actionImprimer(mixed ...$donnees) : JsonResponse
    {
        $code = self::ECHEC;
        $data = [];

        $objet = $this->repository->groupe(id_groupe: $donnees[0]);
        if($objet)
        {
            $data['nom'] = ucwords(string: htmlspecialchars(string: $objet->getNom())) ;
            $utilisateurs = $this->repository->getUtilisateursByGroupe(id_groupe: $donnees[0]);
            // On récupère les utilisateurs du groupe
            $liste = [];
            foreach($utilisateurs as $user)
            {
                $liste[] = ucwords(string: $user->getNom());
            }
            $data['utilisateurs'] = $liste;

            $code = self::SUCCES;
        }

        return new JsonResponse(data: [
            'code' => $code,
            'html' => $data,
        ]);
    }

    protected function lien_a(mixed ...$donnees): string
    {
        return <<<HTML
    <div class="d-sm-inline-flex">
        <a href="#titre" class="text-white mr-1 text-success editBtn h1" title="Modifier" data-id="{$donnees[0]}"><i class="typcn typcn-edit"></i></a>
        <a href="#" class="text-white mr-1 text-secondary showBtn h1" title="Afficher" data-id="{$donnees[0]}" data-toggle="modal" data-target="#afficherBackdrop"><i class="typcn typcn-eye"></i></a>
        <a href="#" class="text-white text-danger deleteBtn h1" title="Supprimer" data-id="{$donnees[0]}" data-nom="{$donnees[1]}"><i class="typcn typcn-trash"></i></a>
    </div>
HTML;
    }

}