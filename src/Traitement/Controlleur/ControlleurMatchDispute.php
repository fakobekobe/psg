<?php

namespace App\Traitement\Controlleur;

use App\Traitement\Abstrait\ControlleurAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Symfony\Component\HttpFoundation\JsonResponse;

class ControlleurMatchDispute extends ControlleurAbstrait
{
    public function rencontre_equipe(mixed ...$donnees) : JsonResponse
    {
        // Les variables
        $rencontre = "";
        $domicile = "";
        $exterieur = "";
        $t_clubs = [];
        $data = [];
        $id_saison = ($donnees[0])->request->all()['match_dispute']['saison'];
        $id_calendrier = ($donnees[0])->request->all()['match_dispute']['calendrier'];
        $lisre_rencontres = ($donnees[1])->getListeRencontres(id_calendrier: $id_calendrier);

        if(!$lisre_rencontres)
        {
            return new JsonResponse(data:['code' => 'ECHEC', 'erreur' => "Cette rencontre n'existe pas."]);
        }

        // On récupère les rencontres
        $rencontre = Utilitaire::checkbox_rencontre(datas: $lisre_rencontres);

        // On récupère le championnat
        $id_championnat = ($donnees[1])->getCalendrier($id_calendrier)->getChampionnat()->getId();

        // Liste des clubs
        $clubs = ($donnees[1])->findListeClubs($id_saison, $id_championnat);
        $domicile = Utilitaire::checkbox_club(datas: $clubs, name: 'domicile');
        $exterieur = Utilitaire::checkbox_club(datas: $clubs, name: 'exterieur');

        $data = [
            'rencontre' => $rencontre,
            'domicile' => $domicile,
            'exterieur' => $exterieur,
        ];


        return new JsonResponse(data:['code' => 'SUCCES', 'data' => $data]);
    }

    public function ajouter(mixed ...$donnees): array
    {
        $objet = ($donnees[0])->new();
        $form = ($donnees[1])->create(type: $donnees[2], data: $objet, options: [
            'attr' => ['id' => $donnees[3]],
        ]);

        $form->handleRequest(request: $donnees[4]);
        $retour['form'] = $form;

        if (($donnees[4])->request->get('rencontre') ?? null){
            $estValide = true;

            $id_rencontre = ($donnees[4])->request->get('rencontre');
            $id_domicile = ($donnees[4])->request->get('domicile');
            $id_exterieur = ($donnees[4])->request->get('exterieur');
            
            $objet = [
                'id_rencontre' => $id_rencontre,
                'id_domicile' => $id_domicile,
                'id_exterieur' => $id_exterieur,
            ];

            // Initialisation du traitement
            ($donnees[0])->initialiserTraitement(em: $donnees[5], repository: $donnees[0]);

            // Appel et récupération des données de la méthode du traitement
            $retour['reponse'] = (($donnees[0])->getTraitement())->actionAjouter($estValide, $objet);
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }

    public function lister(mixed ...$donnees): JsonResponse
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0]); 

        // On récupère la liste des objets
        $liste = ($donnees[0])->findMatchByCalendrier(id_calendrier: $donnees[1]);

        return (($donnees[0])->getTraitement())->actionLister($liste);
    }

}
