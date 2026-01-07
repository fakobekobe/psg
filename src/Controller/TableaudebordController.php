<?php

namespace App\Controller;

use App\Repository\StatistiqueRepository;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/')]
final class TableaudebordController extends AbstractController
{
    private StatistiqueRepository $statistique_repository;

    public function __construct(
        private ManagerRegistry $registry,
    ) {
        $this->statistique_repository = new StatistiqueRepository(registry: $registry);
    }

    #[Route(path: '', name: 'app_tableaudebord')]
    public function index(): Response
    {
        // Les variables
        $id_preponderance_domicile = 1;
        $id_preponderance_exterieur = 2;
        $id_periode_premiere_mt = 1;
        $id_periode_deuxieme_mt = 2;
        
        $stat_classement = $this->stat_classement(
            $this->statistique_repository,
            $id_preponderance_domicile,
            $id_preponderance_exterieur,
            $id_periode_premiere_mt,
            $id_periode_deuxieme_mt,
        );

        dd($stat_classement);

        return $this->render(
            view: 'tableaudebord/index.html.twig',
            parameters: []
        );
    }

    private function stat_classement(mixed $repository, mixed ...$parametres): array
    {
        // Les variables --------------
        $domicile = $parametres[0];
        $exterieur = $parametres[1];
        $premiere_mt = $parametres[2];
        $seconde_mt = $parametres[3];
        $score_domicile = 0;
        $score_exterieur = 0;
        $id_equipe_domicile = 0;
        $id_equipe_exterieur = 0;
        $club = [];
        $classement = [];
        //------------------------------

        // On récupère les saisons
        $saisons = $repository->saisons();
        // On récupère les 2 dernières saisons
        $dernieres_saison = Utilitaire::tableau_portion(donnees: $saisons, nombre: 2, sens: true);
        // On récupère les championnats
        $championnats = $repository->championnats();

        // On parcourt les 2 dernières saisons
        foreach ($dernieres_saison as $saison) {
            // On parcourt les championnats
            foreach ($championnats as $championnat) {
                // On récupère les clubs
                $clubs = $repository->club($saison->getId(), $championnat->getId());
                $club = [];
                $cpt = 0;

                // On parcours les clubs
                foreach ($clubs as $c) {
                    $club[$cpt]['club'] = ucfirst(string: $c->getEquipe()->getNom());
                    $club[$cpt]['id'] = $c->getEquipe()->getId();
                    $club[$cpt]['victoire'] = 0;
                    $club[$cpt]['defaite'] = 0;
                    $club[$cpt]['nul'] = 0;
                    $club[$cpt]['point'] = 0;
                    $club[$cpt]['but_marque'] = 0;
                    $club[$cpt]['but_encaisse'] = 0;
                    $club[$cpt]['but_difference'] = 0;
                    $cpt++;
                }

                // On récupère les calendriers selon le championnat
                // $calendriers est une liste qui contient des dictionnaires ['id','libelle']
                $calendriers = $repository->getCalendriersByChampionnat($championnat->getId());

                // On parcourt les calendriers  
                foreach ($calendriers as $calendrier) {
                    // On récupère les données statistiques
                    $data = $repository->findStatistiqueBySaisonByChampionnatByCalendrier(
                        id_saison: $saison->getId(),
                        id_championnat: $championnat->getId(),
                        id_calendrier: $calendrier['id']
                    );

                    if (!$data) break; // On sort de la boucle au cas ou nous n'avons pas de match joué selon le calendrier

                    // On récupère les rencontres selon la saison et le calendrier
                    $rencontres = $repository->rencontres($saison->getId(), $calendrier['id']);

                    // On parcourt les rencontres 
                    foreach ($rencontres as $rencontre) {
                        // On initialise les valeurs
                        $id_equipe_domicile = 0;
                        $id_equipe_exterieur = 0;
                        $score_domicile = 0;
                        $score_exterieur = 0;
                        $rang_domicile = 0;
                        $rang_exterieur = 0;

                        // On parcours les datas ------------------------------
                        foreach ($data as $donnees) {
                            if ($donnees['rencontre'] == $rencontre->getId()) {

                                if ($donnees['preponderance'] == $domicile) {
                                    if ($donnees['periode'] == $premiere_mt) {
                                        $score_domicile += ($donnees['statistique'])->getScore();
                                    } else if ($donnees['periode'] == $seconde_mt) {
                                        $score_domicile += ($donnees['statistique'])->getScore();
                                        $id_equipe_domicile = $donnees['equipe']; // On récupère l'id de l'équipe à domicile
                                    }
                                } else if ($donnees['preponderance'] == $exterieur) {
                                    if ($donnees['periode'] == $premiere_mt) {
                                        $score_exterieur += ($donnees['statistique'])->getScore();
                                    } else if ($donnees['periode'] == $seconde_mt) {
                                        $score_exterieur += ($donnees['statistique'])->getScore();
                                        $id_equipe_exterieur = $donnees['equipe']; // On récupère l'id de l'équipe à l'extérieur
                                    }
                                }
                            }
                        }

                        // On effectue le classement [1-2] à partir de la 2e journée --------------------------
                        if ($rencontre->getCalendrier()->getJournee()->getNumero() > 1 and $id_equipe_domicile) {
                            for ($i = 0; $i < count(value: $club); $i++) {
                                if ($club[$i]['id'] == $id_equipe_domicile) {
                                    $rang_domicile = $i + 1;
                                } else if ($club[$i]['id'] == $id_equipe_exterieur) {
                                    $rang_exterieur = $i + 1;
                                }
                            }

                            $code = Utilitaire::categorie_classement(rang_domicile: $rang_domicile, rang_exterieur: $rang_exterieur);

                            if (empty($classement[$championnat->getNom()])) {
                                if ($score_domicile > 0 and $score_exterieur > 0) {
                                    $classement[$championnat->getNom()][$code][Utilitaire::EM2] = 1;
                                    $classement[$championnat->getNom()][$code][Utilitaire::EM1] = 0;
                                } else {
                                    $classement[$championnat->getNom()][$code][Utilitaire::EM1] = 1;
                                    $classement[$championnat->getNom()][$code][Utilitaire::EM2] = 0;
                                }
                            } else {
                                if (!array_key_exists(key: $code, array: $classement[$championnat->getNom()])) {
                                    // Le code n'existe pas on l'ajoute pour la premier fois                   
                                    if ($score_domicile > 0 and $score_exterieur > 0) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::EM2] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::EM1] = 0;
                                    } else {
                                        $classement[$championnat->getNom()][$code][Utilitaire::EM1] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::EM2] = 0;
                                    }
                                } else {
                                    // Le code existe on l'incrémente
                                    if ($score_domicile > 0 and $score_exterieur > 0) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::EM2] += 1;
                                    } else {
                                        $classement[$championnat->getNom()][$code][Utilitaire::EM1] += 1;
                                    }
                                }
                            }
                        }

                        // On sauvegarde les statistiques par équipe -------------------------------
                        for ($j = 0; $j < count(value: $club); $j++) {
                            if ($club[$j]['id'] == $id_equipe_domicile) {
                                // SCORE--------------------------                    
                                if ($score_domicile > $score_exterieur) {
                                    $club[$j]['victoire'] += 1; // VICTOIRE
                                    $club[$j]['point'] += Utilitaire::POINT_VICTOIRE; // POINT                            
                                    $club[$j]['performance'][] = Utilitaire::VICTOIRE; // PERFORMANCE                            
                                } else if ($score_domicile < $score_exterieur) {
                                    $club[$j]['defaite'] += 1; // DEFAITE
                                    $club[$j]['performance'][] = Utilitaire::DEFAITE; // PERFORMANCE
                                } else {
                                    $club[$j]['nul'] += 1; // NUL
                                    $club[$j]['point'] += Utilitaire::POINT_NUL; // POINT
                                    $club[$j]['performance'][] = Utilitaire::NUL; // PERFORMANCE
                                }
                                //-------------------------------

                                // BUT -------------------------
                                $club[$j]['but_marque'] += $score_domicile;
                                $club[$j]['but_encaisse'] += $score_exterieur;
                                $club[$j]['but_difference'] = $club[$j]['but_marque'] - $club[$j]['but_encaisse'];
                                //--------------------------                        
                            } else if ($club[$j]['id'] == $id_equipe_exterieur) {
                                // SCORE--------------------------                    
                                if ($score_exterieur > $score_domicile) {
                                    $club[$j]['victoire'] += 1; // VICTOIRE
                                    $club[$j]['point'] += Utilitaire::POINT_VICTOIRE; // POINT                            
                                    $club[$j]['performance'][] = Utilitaire::VICTOIRE; // PERFORMANCE                            
                                } else if ($score_exterieur < $score_domicile) {
                                    $club[$j]['defaite'] += 1; // DEFAITE
                                    $club[$j]['performance'][] = Utilitaire::DEFAITE; // PERFORMANCE
                                } else {
                                    $club[$j]['nul'] += 1; // NUL
                                    $club[$j]['point'] += Utilitaire::POINT_NUL; // POINT
                                    $club[$j]['performance'][] = Utilitaire::NUL; // PERFORMANCE
                                }
                                //-------------------------------

                                // BUT -------------------------
                                $club[$j]['but_marque'] += $score_exterieur;
                                $club[$j]['but_encaisse'] += $score_domicile;
                                $club[$j]['but_difference'] = $club[$j]['but_marque'] - $club[$j]['but_encaisse'];
                                //--------------------------                        
                            }
                        }
                    }

                    // On effectue le classement des équipes par points, difference de buts et buts marqués
                    for ($i = 0; $i < (count(value: $club) - 1); $i++) // On parcourt la première équipe
                    {
                        for ($j = $i + 1; $j < count(value: $club); $j++) // On parcourt la seconde équipe
                        {
                            if ($club[$i]['point'] < $club[$j]['point']) // On compare les points
                            {
                                // On sauvegarde les données
                                $equipe = $club[$i]['club'];
                                $id = $club[$i]['id'];
                                $victoire = $club[$i]['victoire'];
                                $defaite = $club[$i]['defaite'];
                                $nul = $club[$i]['nul'];
                                $point = $club[$i]['point'];
                                $but_marque = $club[$i]['but_marque'];
                                $but_encaisse = $club[$i]['but_encaisse'];
                                $but_difference = $club[$i]['but_difference'];
                                $performance = $club[$i]['performance'];

                                // On change les positions------
                                $club[$i]['club'] = $club[$j]['club'];
                                $club[$i]['id'] = $club[$j]['id'];
                                $club[$i]['victoire'] = $club[$j]['victoire'];
                                $club[$i]['defaite'] = $club[$j]['defaite'];
                                $club[$i]['nul'] = $club[$j]['nul'];
                                $club[$i]['point'] = $club[$j]['point'];
                                $club[$i]['but_marque'] = $club[$j]['but_marque'];
                                $club[$i]['but_encaisse'] = $club[$j]['but_encaisse'];
                                $club[$i]['but_difference'] = $club[$j]['but_difference'];
                                $club[$i]['performance'] = $club[$j]['performance'];

                                $club[$j]['club'] = $equipe;
                                $club[$j]['id'] = $id;
                                $club[$j]['victoire'] = $victoire;
                                $club[$j]['defaite'] = $defaite;
                                $club[$j]['nul'] = $nul;
                                $club[$j]['point'] = $point;
                                $club[$j]['but_marque'] = $but_marque;
                                $club[$j]['but_encaisse'] = $but_encaisse;
                                $club[$j]['but_difference'] = $but_difference;
                                $club[$j]['performance'] = $performance;
                            } else if ($club[$i]['point'] == $club[$j]['point']) {
                                if ($club[$i]['but_difference'] < $club[$j]['but_difference']) // On compare la différence de but
                                {
                                    // On sauvegarde les données
                                    $equipe = $club[$i]['club'];
                                    $id = $club[$i]['id'];
                                    $victoire = $club[$i]['victoire'];
                                    $defaite = $club[$i]['defaite'];
                                    $nul = $club[$i]['nul'];
                                    $point = $club[$i]['point'];
                                    $but_marque = $club[$i]['but_marque'];
                                    $but_encaisse = $club[$i]['but_encaisse'];
                                    $but_difference = $club[$i]['but_difference'];
                                    $performance = $club[$i]['performance'];

                                    // On change les positions------
                                    $club[$i]['club'] = $club[$j]['club'];
                                    $club[$i]['id'] = $club[$j]['id'];
                                    $club[$i]['victoire'] = $club[$j]['victoire'];
                                    $club[$i]['defaite'] = $club[$j]['defaite'];
                                    $club[$i]['nul'] = $club[$j]['nul'];
                                    $club[$i]['point'] = $club[$j]['point'];
                                    $club[$i]['but_marque'] = $club[$j]['but_marque'];
                                    $club[$i]['but_encaisse'] = $club[$j]['but_encaisse'];
                                    $club[$i]['but_difference'] = $club[$j]['but_difference'];
                                    $club[$i]['performance'] = $club[$j]['performance'];

                                    $club[$j]['club'] = $equipe;
                                    $club[$j]['id'] = $id;
                                    $club[$j]['victoire'] = $victoire;
                                    $club[$j]['defaite'] = $defaite;
                                    $club[$j]['nul'] = $nul;
                                    $club[$j]['point'] = $point;
                                    $club[$j]['but_marque'] = $but_marque;
                                    $club[$j]['but_encaisse'] = $but_encaisse;
                                    $club[$j]['but_difference'] = $but_difference;
                                    $club[$j]['performance'] = $performance;
                                } else if ($club[$i]['but_difference'] == $club[$j]['but_difference']) {
                                    if ($club[$i]['but_marque'] < $club[$j]['but_marque']) // On compare le total de but marqué
                                    {
                                        // On sauvegarde les données
                                        $equipe = $club[$i]['club'];
                                        $id = $club[$i]['id'];
                                        $victoire = $club[$i]['victoire'];
                                        $defaite = $club[$i]['defaite'];
                                        $nul = $club[$i]['nul'];
                                        $point = $club[$i]['point'];
                                        $but_marque = $club[$i]['but_marque'];
                                        $but_encaisse = $club[$i]['but_encaisse'];
                                        $but_difference = $club[$i]['but_difference'];
                                        $performance = $club[$i]['performance'];

                                        // On change les positions------
                                        $club[$i]['club'] = $club[$j]['club'];
                                        $club[$i]['id'] = $club[$j]['id'];
                                        $club[$i]['victoire'] = $club[$j]['victoire'];
                                        $club[$i]['defaite'] = $club[$j]['defaite'];
                                        $club[$i]['nul'] = $club[$j]['nul'];
                                        $club[$i]['point'] = $club[$j]['point'];
                                        $club[$i]['but_marque'] = $club[$j]['but_marque'];
                                        $club[$i]['but_encaisse'] = $club[$j]['but_encaisse'];
                                        $club[$i]['but_difference'] = $club[$j]['but_difference'];
                                        $club[$i]['performance'] = $club[$j]['performance'];

                                        $club[$j]['club'] = $equipe;
                                        $club[$j]['id'] = $id;
                                        $club[$j]['victoire'] = $victoire;
                                        $club[$j]['defaite'] = $defaite;
                                        $club[$j]['nul'] = $nul;
                                        $club[$j]['point'] = $point;
                                        $club[$j]['but_marque'] = $but_marque;
                                        $club[$j]['but_encaisse'] = $but_encaisse;
                                        $club[$j]['but_difference'] = $but_difference;
                                        $club[$j]['performance'] = $performance;
                                    }
                                }
                            }
                        }
                    }
                }


                // On effectue les calculs sur le classement
                if(!empty($classement[$championnat->getNom()]))
                {
                    foreach ($classement[$championnat->getNom()] as $key => $classe) {
                        $classement[$championnat->getNom()][$key][Utilitaire::TR] = $classement[$championnat->getNom()][$key][Utilitaire::EM1] + $classement[$championnat->getNom()][$key][Utilitaire::EM2];
                        $classement[$championnat->getNom()][$key][Utilitaire::PEM1] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::EM1] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::PEM2] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::EM2] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI] = ($classement[$championnat->getNom()][$key][Utilitaire::PEM1] > Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::PEM2] > Utilitaire::PARI) ? true : false;
                    }
                }
            }
        }

        return $classement;
    }
}
