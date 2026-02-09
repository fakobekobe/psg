<?php

namespace App\Traitement\Controlleur;

use App\Traitement\Abstrait\ControlleurAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Symfony\Component\HttpFoundation\JsonResponse;

class ControlleurStatistique extends ControlleurAbstrait
{
    public function ajouter(mixed ...$donnees): array
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

        $retour = [];

        // Liste des matchs selon la rencontre
        $liste_match = ($donnees[1])->getListeMatchByRencontre(id_rencontre: $id_rencontre);

        foreach ($liste_match as $match) {
            // On vérifie si cette équipe (MatchDispute) possède déjà des statistiques dans la période
            if (($donnees[1])->findStatistiqueByMatchByPeriode(id_match: $match->getId(), id_periode: $id_periode)) {
                $retour['reponse'] = new JsonResponse(data: ['code' => 'ECHEC', 'erreur' => "Ce match possède déjà des statistiques de la [{$periode->getLibelle()}]."]);
                return $retour;
            }

            // Pas de statistique
            $statistique = ($donnees[1])->new(); // On crée l'objet statistique            

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

        $retour['reponse'] = new JsonResponse(data: ['code' => 'SUCCES']);
        return $retour;
    }

    public function lister(mixed ...$donnees): JsonResponse
    {
        // Les variables à charger avec les valeurs de la configuration depuis le repository
        $id_preponderance_domicile = 1;
        $id_preponderance_exterieur = 2;

        $statistique = null;
        $data = [];
        $cpt = 0;

        // On récupère la liste des matchs
        $liste_match = ($donnees[0])->getListeMatchByRencontre($donnees[1]);
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

        // On initie le traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0], em: $donnees[2]); 

        // On exécute l'action lister
        return (($donnees[0])->getTraitement())->actionLister($data);
    }  

    public function check(mixed ...$donnees): JsonResponse
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0], em: $donnees[3]);

        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($donnees[0])->getTraitement()->actionCheck($donnees[1], $donnees[2]);
    }

    public function modifier(mixed ...$donnees): JsonResponse
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
        $liste_match = ($donnees[1])->getListeMatchByRencontre(id_rencontre: $id_rencontre);

        foreach ($liste_match as $match) {            
            // On récupère l'objet statistique
            $statistique = ($donnees[1])->findStatistiqueByMatchByPeriode($match->getId(), $id_periode);

            if ($match->getPreponderance()->getId() == $id_preponderance_domicile) {
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

    public function supprimer(mixed ...$donnees): JsonResponse
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(em: $donnees[1], repository: $donnees[0]);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($donnees[0])->getTraitement()->actionSupprimer($donnees[2], $donnees[3]);
    }     
}
