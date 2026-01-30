<?php

namespace App\Traitement\Model;

use App\Traitement\Abstrait\TraitementAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TraitementDroitGroupePage extends TraitementAbstrait
{
    public function __construct(
        protected ?EntityManagerInterface $em = null,
        protected ?FormInterface $form = null,
        protected ?ServiceEntityRepository $repository = null,
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
        $nb = count(value: $donnees[0]);

        foreach ($donnees[0] as $data) {
            $id = $data['id'];
            $groupe = ucfirst(string: htmlspecialchars(string: $data['groupe']));
            $droit = ucfirst(string: htmlspecialchars(string: $data['droit']));
            $pages = ucfirst(string: htmlspecialchars(string: $data['pages']));
            $nom = $groupe . ' : ' . $droit;

            $i++;
            $v = ($i != $nb) ? '!x!' : '';
            $tab .= $i . $separateur .
                $groupe . $separateur .
                $droit . $separateur .
                $pages . $separateur .
                $this->lien_a($id, $nom) . $v;
        }

        return $tab;
    }

    protected function actionAjouterSucces(mixed ...$donnees) : JsonResponse
    {
        $code = self::ECHEC;
        $utilisateurEnCours = null;
        if(!empty($donnees['donnees'][1]['user'])) $utilisateurEnCours = $donnees['donnees'][1]['user'];

        if (!empty($donnees['donnees'][1]['groupe']) and !empty($donnees['donnees'][1]['droit']) and !empty($donnees['donnees'][1]['page'])) {
            foreach ($donnees['donnees'][1]['groupe'] as $id_groupe) {
                foreach($donnees['donnees'][1]['droit'] as $id_droit)
                {
                    foreach ($donnees['donnees'][1]['page'] as $id_page) {
                        if (
                            $page = $this->repository->page(id_page: $id_page) and
                            $droit = $this->repository->droit(id_droit: $id_droit) and
                            $groupe = $this->repository->groupe(id_groupe: $id_groupe)
                            ) {
                                if (!$this->repository->existe(groupe: $groupe, droit: $droit, page: $page)) {
                                    $data = $this->repository->new();
                                    $data->setGroupe($groupe);
                                    $data->setDroit($droit);
                                    $data->setPage($page);
                                    $this->em->persist(object: $data);
                                    $this->em->flush();

                                    // On ajoute les roles de l'utilisateurs
                                    Utilitaire::AjouteDroitUtilisateur(id_groupe: $id_groupe, objetRepo: $this->repository, utilisateurEnCours: $utilisateurEnCours);
                            
                                    $code = self::SUCCES;
                                }
                        }
                    }
                }
            }
        }

        return new JsonResponse(data: [
            'code' => $code,
        ]);
    }

    public function actionSupprimer(mixed ...$donnees): JsonResponse
    {
        $id_droit_groupe_page = $donnees[0] ?? $donnees['donnees'];
        $mixe = $donnees[0] ?? $donnees['donnees'];
        if(is_array(value: $mixe))
        {
            $id_droit_groupe_page = $mixe['id'];
        }

        $data = $this->repository->findOneBy(criteria: ['id' => $id_droit_groupe_page]);
        if($data)
        {
            $objet['id_groupe'] = $data->getGroupe()->getId();
            $objet['id_droit'] = $data->getDroit()->getId();
            $objet['data'] = $this->repository->findBy(criteria: ['groupe' => $data->getGroupe()->getId(), 'droit' => $data->getDroit()->getId()]);
            if(is_array(value: $mixe))
            {
                $objet['user'] = $mixe['user'];
            }
            
            return $this->actionSupprimerSucces(objet: $objet);
        }else{
            return $this->actionSupprimerEchec();
        }
    }

    protected function actionSupprimerSucces(mixed $objet): JsonResponse
    {
        $utilisateurEnCours = null;
        if(!empty($objet['user'])) $utilisateurEnCours = $objet['user'];
        
        // On Supprime tous les roles du groupe de la liste des utilisateurs du groupe
        Utilitaire::SupprimeDroitUtilisateur(id_groupe: $objet['id_groupe'], objetRepo: $this->repository, id_droit: $objet['id_droit'], utilisateurEnCours: $utilisateurEnCours);
        
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
        $this->actionSupprimer(donnees: $objet);

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
        $droits = ($donnees[0])->droits();
        $groupes = ($donnees[0])->groupes();
        $pages = ($donnees[0])->pages();

        $reponse = [];
        
        if ($groupes) {
            $reponse['groupe'] = Utilitaire::chaineFormulaire(tableau: $groupes, class: 'cocher_groupe', nom: 'getNom', input_name: 'groupe', type: 'groupe', data: $donnees[1], objetRepository: $donnees[0]);
        } else {
            $reponse['groupe'] = '<h6>Aucun Groupe disponible</h6>';
        }

        if ($droits) {
            $reponse['droit'] = Utilitaire::chaineFormulaire(tableau: $droits, class: 'cocher_droit', nom: 'getNom', input_name: 'droit', type: 'droit', data: $donnees[1], objetRepository: $donnees[0]);
        } else {
            $reponse['droit'] = '<h6>Aucun Droit disponible</h6>';
        }

        if ($pages) {
            $reponse['page'] = Utilitaire::chaineFormulaire(tableau: $pages, class: 'cocher_page', nom: 'getNom', input_name: 'page', type: 'page', data: $donnees[1], objetRepository: $donnees[0]);
        } else {
            $reponse['page'] = '<h6>Aucune Page disponible</h6>';
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
        <a href="#" class="text-white text-danger deleteBtn h1" title="Supprimer" data-id="{$donnees[0]}" data-nom="{$donnees[1]}"><i class="typcn typcn-trash"></i></a>
    </div>
HTML;
    }

}