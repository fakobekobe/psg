<?php

namespace App\Traitement\Controlleur;

use App\Traitement\Abstrait\ControlleurAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Symfony\Component\HttpFoundation\JsonResponse;

class ControlleurMatchDispute extends ControlleurAbstrait
{
    public function rencontre_equipe(mixed ...$donnees): JsonResponse
    {
        // Les variables
        $rencontre = "";
        $domicile = "";
        $exterieur = "";
        $data = [];
        $id_saison = ($donnees[0])->request->all()['match_dispute']['saison'];
        $id_calendrier = ($donnees[0])->request->all()['match_dispute']['calendrier'];

        // On récupère la liste des rencontres
        $liste_rencontres = $this->liste_rencontre($donnees[1], $id_calendrier, $id_saison);
        if (!$liste_rencontres[0]) {
            return new JsonResponse(data: ['code' => 'ECHEC', 'erreur' => "Cette rencontre n'existe pas."]);
        }

        // On récupère les rencontres
        $rencontre = Utilitaire::checkbox_rencontre(datas: $liste_rencontres[1]);

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


        return new JsonResponse(data: ['code' => 'SUCCES', 'data' => $data]);
    }

    public function ajouter(mixed ...$donnees): array
    {
        $objet = ($donnees[0])->new();
        $form = ($donnees[1])->create(type: $donnees[2], data: $objet, options: [
            'attr' => ['id' => $donnees[3]],
        ]);

        $periode = ($donnees[1])->create(type: $donnees[6]);

        $form->handleRequest(request: $donnees[4]);
        $retour['form'] = $form;
        $retour['periode'] = $periode;

        if (($donnees[4])->request->get('rencontre') ?? null) {
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
        $liste = ($donnees[0])->findMatchByCalendrier(id_calendrier: $donnees[1], id_saison: $donnees[2]);

        return (($donnees[0])->getTraitement())->actionLister($liste);
    }

    public function supprimer(mixed ...$donnees): JsonResponse
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(em: $donnees[1], repository: $donnees[0]);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($donnees[0])->getTraitement()->actionSupprimer($donnees[2]);
    }

    public function periode(mixed ...$donnees): JsonResponse
    {
        // Les variables à charger avec les valeurs du paramètre depuis le repository
        $repo_parametre = ($donnees[0])->getParametre();
        $id_preponderance_domicile = $repo_parametre->getDomicile();
        $id_preponderance_exterieur = $repo_parametre->getExterieur();
        $data = [];

        // On récupère la liste des matchs selon l'id_rencontre
        $liste_match = ($donnees[0])->findBy(['rencontre' => $donnees[1]]);

        if (!$liste_match) {
            return new JsonResponse(data: [
                'code' => 'ECHEC',
                'data' => "Cette rencontre n'existe pas."
            ]);
        }

        foreach ($liste_match as $match) {
            if ($match->getPreponderance()->getId() == $id_preponderance_domicile) {
                $logo = Utilitaire::afficher_image_circulaire(path: $match->getEquipeSaison()->getEquipe()->getLogo());
                $equipe = $match->getEquipeSaison()->getEquipe()->getNom();

                $data['domicile'] = <<<HTML
                {$logo}
				<h4>{$equipe}</h4>
HTML;
            } else if ($match->getPreponderance()->getId() == $id_preponderance_exterieur) {
                $logo = Utilitaire::afficher_image_circulaire(path: $match->getEquipeSaison()->getEquipe()->getLogo());
                $equipe = $match->getEquipeSaison()->getEquipe()->getNom();

                $data['exterieur'] = <<<HTML
                {$logo}
				<h4>{$equipe}</h4>
HTML;
            }
        }

        return new JsonResponse(data: [
            'code' => 'SUCCES',
            'data' => $data
        ]);
    }
    private function liste_rencontre(mixed ...$donnees): array
    {
        $retour = [];

        $liste_rencontres = ($donnees[0])->getListeRencontres(id_calendrier: $donnees[1], id_saison: $donnees[2]);
        $liste_matchs = ($donnees[0])->findMatchByCalendrier(id_calendrier: $donnees[1], id_saison: $donnees[2]);
        $t_rencontres = [];
        $r_trouve = false;

        // On conserve les rencontres qui n'ont fait l'objet de match
        foreach ($liste_rencontres as $rencontre) {
            $r_trouve = false;
            foreach ($liste_matchs as $match) {
                if ($rencontre->getId() == $match->getRencontre()->getId()) {
                    $r_trouve = true;
                    break;
                }
            }

            // On conserve la rencontre
            if (!$r_trouve) {
                $t_rencontres[] = $rencontre;
            }
        }

        $retour = [
            0 => $liste_rencontres,
            1 => $t_rencontres,
        ];

        return $retour;
    }

}
