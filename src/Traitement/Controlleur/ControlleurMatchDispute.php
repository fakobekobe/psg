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
        $liste_rencontres = $this->liste_rencontre($donnees[1], $id_calendrier);
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
        $liste = ($donnees[0])->findMatchByCalendrier(id_calendrier: $donnees[1]);

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
        // Les variables à charger avec les valeurs de la configuration depuis le repository
        $id_preponderance_domicile = 1;
        $id_preponderance_exterieur = 2;
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

    public function statistique(mixed ...$donnees): JsonResponse
    {
        // Les variables à charger avec les valeurs de la configuration depuis le repository
        $id_preponderance_domicile = 1;
        $id_preponderance_exterieur = 2;

        // Les variables
        $score_d = (int)($donnees[0])->request->get('score_d');
        $score_e = (int)($donnees[0])->request->get('score_e');
        $possession_d = (float)($donnees[0])->request->get('possession_d');
        $possession_e = (int)($donnees[0])->request->get('possession_e');
        $total_tir_d = (int)($donnees[0])->request->get('total_tir_d');
        $total_tir_e = (int)($donnees[0])->request->get('total_tir_e');
        $tir_cadre_d = (int)($donnees[0])->request->get('tir_cadre_d');
        $tir_cadre_e = (int)($donnees[0])->request->get('tir_cadre_e');
        $grosse_chance_d = (int)($donnees[0])->request->get('grosse_chance_d');
        $grosse_chance_e = (int)($donnees[0])->request->get('grosse_chance_e');
        $corner_d = (int)($donnees[0])->request->get('corner_d');
        $corner_e = (int)($donnees[0])->request->get('corner_e');
        $carton_jaune_d = (int)($donnees[0])->request->get('carton_jaune_d');
        $carton_jaune_e = (int)($donnees[0])->request->get('carton_jaune_e');
        $carton_rouge_d = (int)($donnees[0])->request->get('carton_rouge_d');
        $carton_rouge_e = (int)($donnees[0])->request->get('carton_rouge_e');
        $hors_jeu_d = (int)($donnees[0])->request->get('hors_jeu_d');
        $hors_jeu_e = (int)($donnees[0])->request->get('hors_jeu_e');
        $coup_franc_d = (int)($donnees[0])->request->get('coup_franc_d');
        $coup_franc_e = (int)($donnees[0])->request->get('coup_franc_e');
        $touche_d = (int)($donnees[0])->request->get('touche_d');
        $touche_e = (int)($donnees[0])->request->get('touche_e');
        $faute_d = (int)($donnees[0])->request->get('faute_d');
        $faute_e = (int)($donnees[0])->request->get('faute_e');
        $tacle_d = (int)($donnees[0])->request->get('tacle_d');
        $tacle_e = (int)($donnees[0])->request->get('tacle_e');
        $arret_d = (int)($donnees[0])->request->get('arret_d');
        $arret_e = (int)($donnees[0])->request->get('arret_e');

        $id_periode = (int)($donnees[0])->request->get('periode');
        $id_rencontre = (int)($donnees[0])->request->get('rencontre');
        $periode = ($donnees[1])->periode($id_periode);

        // Liste des matchs selon la rencontre
        $liste_match = ($donnees[1])->findBy(['rencontre' => $id_rencontre]);

        foreach ($liste_match as $match) {
            // On vérifie si cette équipe (MatchDispute) possède déjà des statistiques dans la période
            if (($donnees[1])->findStatistiqueByMatchByPeriode(id_match: $match->getId(), id_periode: $id_periode)) {
                return new JsonResponse(data: ['code' => 'ECHEC', 'erreur' => "Ce match possède déjà des statistiques de la [{$periode->getLibelle()}]."]);
            }

            // Pas de statistique
            $statistique = ($donnees[1])->statistique(); // On crée l'objet statistique            

            if ($match->getPreponderance()->getId() == $id_preponderance_domicile) {
                $statistique->setMatchDispute($match);
                $statistique->setPeriode($periode);
                $statistique->setScore($score_d);
                $statistique->setPossession($possession_d);
                $statistique->setTotalTir($total_tir_d);
                $statistique->setTirCadre($tir_cadre_d);
                $statistique->setGrosseChance($grosse_chance_d);
                $statistique->setCorner($corner_d);
                $statistique->setCartonJaune($carton_jaune_d);
                $statistique->setCartonRouge($carton_rouge_d);
                $statistique->setHorsJeu($hors_jeu_d);
                $statistique->setCoupsFranc($coup_franc_d);
                $statistique->setTouche($touche_d);
                $statistique->setFaute($faute_d);
                $statistique->setTacle($tacle_d);
                $statistique->setArret($arret_d);
                ($donnees[2])->persist($statistique);
            } else if ($match->getPreponderance()->getId() == $id_preponderance_exterieur) {
                $statistique->setMatchDispute($match);
                $statistique->setPeriode($periode);
                $statistique->setScore($score_e);
                $statistique->setPossession($possession_e);
                $statistique->setTotalTir($total_tir_e);
                $statistique->setTirCadre($tir_cadre_e);
                $statistique->setGrosseChance($grosse_chance_e);
                $statistique->setCorner($corner_e);
                $statistique->setCartonJaune($carton_jaune_e);
                $statistique->setCartonRouge($carton_rouge_e);
                $statistique->setHorsJeu($hors_jeu_e);
                $statistique->setCoupsFranc($coup_franc_e);
                $statistique->setTouche($touche_e);
                $statistique->setFaute($faute_e);
                $statistique->setTacle($tacle_e);
                $statistique->setArret($arret_e);
                ($donnees[2])->persist($statistique);
            }

            ($donnees[2])->flush();
        }

        return new JsonResponse(data: ['code' => 'SUCCES']);
    }

    private function liste_rencontre(mixed ...$donnees): array
    {
        $retour = [];

        $liste_rencontres = ($donnees[0])->getListeRencontres(id_calendrier: $donnees[1]);
        $liste_matchs = ($donnees[0])->findMatchByCalendrier(id_calendrier: $donnees[1]);
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

    public function liste_statistique(mixed ...$donnees): JsonResponse
    {
        // Les variables à charger avec les valeurs de la configuration depuis le repository
        $id_preponderance_domicile = 1;
        $id_preponderance_exterieur = 2;

        $statistique = null;
        $data = [];
        $cpt = 0;

        // On récupère la liste des matchs
        $liste_match = ($donnees[0])->findBy(['rencontre' => $donnees[1]]);
        $periodes = ($donnees[0])->periodes();

        // On parcours les périodes
        foreach ($periodes as $periode) {

            // On récutère le tableau des statistiques des 2 clubs
            foreach ($liste_match as $match) {
                $statistique = ($donnees[0])->findStatistiqueByMatchByPeriode(id_match: $match->getId(), id_periode: $periode->getId());
                if ($statistique) {
                    if ($match->getPreponderance()->getId() == $id_preponderance_domicile) {
                        $data[$cpt]['domicile'] = $statistique;
                    } else if ($match->getPreponderance()->getId() == $id_preponderance_exterieur) {
                        $data[$cpt]['exterieur'] = $statistique;
                    }
                }
            }
            $cpt++;
        }

        // On charge les données de retour 
        if ($data) {
            return new JsonResponse(data: ['code' => 'SUCCES', 'html' => $this->chaine_data(donnees: $data)]);
        } else {
            return new JsonResponse(data: ['code' => 'ECHEC']);
        }
    }

    private function chaine_data(mixed ...$donnees): string
    {
        $tab = "";
        $i = 0;
        $separateur = ';x;';
        $nb = count(value: $donnees['donnees']);

        foreach ($donnees['donnees'] as $data) {

            $calendrier = ($data['domicile'])->getMatchDispute()->getRencontre()->getCalendrier()->getJournee()->getDescription();
            $club_domicile = ($data['domicile'])->getMatchDispute()->getEquipeSaison()->getEquipe()->getNom();
            $club_exterieur  = ($data['exterieur'])->getMatchDispute()->getEquipeSaison()->getEquipe()->getNom();
            $periode = ($data['exterieur'])->getPeriode()->getLibelle();
            $score = ($data['domicile'])->getScore() . ' - ' . ($data['exterieur'])->getScore();

            $nom = $calendrier . ' => ' . $club_domicile . ' VS ' . $club_exterieur . ' => ' . $periode . ' => ' . $score;

            $i++;
            $v = ($i != $nb) ? '!x!' : '';
            $tab .=  $i . $separateur .
                $calendrier . $separateur .
                $club_domicile . $separateur .
                $club_exterieur . $separateur .
                $periode . $separateur .
                $score . $separateur .
                $this->lien_a(($data['domicile'])->getMatchDispute()->getRencontre()->getId(), ($data['exterieur'])->getPeriode()->getId(), $nom) . $v;
        }
        return $tab;
    }

    private function lien_a(mixed ...$donnees): string
    {
        return <<<HTML
    <div class="d-sm-inline-flex">
        <a href="#" class="text-white mr-1 text-success editStatBtn h1" title="Statistiques" data-id_rencontre="{$donnees[0]}" data-id_periode="{$donnees[1]}"><i class="typcn typcn-edit"></i></a>
        <a href="#" class="text-white text-danger deleteStatBtn h1" title="Supprimer" data-id_rencontre="{$donnees[0]}" data-id_periode="{$donnees[1]}" data-nom="{$donnees[2]}"><i class="typcn typcn-trash"></i></a>
    </div>
HTML;
    }
}
