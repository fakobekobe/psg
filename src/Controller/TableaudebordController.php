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

        $stat = $this->stat_classement(
            $this->statistique_repository,
            $id_preponderance_domicile,
            $id_preponderance_exterieur,
            $id_periode_premiere_mt,
            $id_periode_deuxieme_mt,
        );

        $stat_classement = $stat["classement"];
        $stat_2EM = $stat["deux_equipe_marquent"];
        $stat_2EM = $this->EM2_Oui_Non(repository: $this->statistique_repository, donnees: $stat_2EM);
        $but_mi_temps = $stat["but_mi_temps"];
        $nul_chaque_mi_temps = $stat["nul_chaque_mi_temps"];
        $nul_chaque_mi_temps = $this->nul_chaque_mi_temps(repository: $this->statistique_repository, donnees: $nul_chaque_mi_temps);

        return $this->render(
            view: 'tableaudebord/index.html.twig',
            parameters: [
                'classements' => $stat_classement,
                'em2_oui_non' => $stat_2EM,
                "but_mi_temps" => $but_mi_temps,
                "nul_chaque_mi_temps" => $nul_chaque_mi_temps,
            ]
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
        $but_mi_temps = [];
        $donnees_but_mi_temps = [];
        $data_2EM_O_N = [];
        $donnees_2EM_O_N = [];
        $score_PM_domicile = 0;
        $score_2M_domicile = 0;
        $score_PM_exterieur = 0;
        $score_2M_exterieur = 0;
        $NPM = 0; // Nul première mi-temps
        $BPM = 0; // But première mi-temps
        $N2PM = 0; // Nul deuxième mi-temps
        $B2PM = 0; // But deuxième mi-temps
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
                $cpte = 0;
                $cptn = 0;

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

                // Gestion de l'analyse les 2 équipes se marquent ou pas ------------
                $liste_calendrier_jouer = $repository->findCalendriersBySaisonByChampionnat($saison->getId(), $championnat->getId());
                if ($liste_calendrier_jouer) {
                    $calendrier_championnat = $repository->getCalendriersByChampionnat(id_championnat: $championnat->getId());
                    $liste_calend_jouer = [];

                    foreach ($liste_calendrier_jouer as $calend) {
                        $liste_calend_jouer[] = $calend['id'];
                    }

                    $tableau_calendrier = [];

                    foreach ($calendrier_championnat as $calend) {
                        $tableau_calendrier[] = $calend['id'];
                    }

                    // On vérifie s'il y a un calendrier non disputé
                    foreach ($tableau_calendrier as $calend) {
                        if (!in_array(needle: $calend, haystack: $liste_calend_jouer)) {
                            $data_2EM_O_N['id_calendrier'] = $calend;
                            $data_2EM_O_N['id_saison'] = $saison->getId();
                            $data_2EM_O_N['id_championnat'] = $championnat->getId();
                            break;
                        } else {
                            $data_2EM_O_N['id_calendrier_precedent'] = $calend;
                            $data_2EM_O_N['id_calendrier'] = 0;
                            $data_2EM_O_N['id_saison'] = 0;
                            $data_2EM_O_N['id_championnat'] = 0;
                        }
                    }
                }

                // ------------------------------------------------------------------

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
                                        $score_PM_domicile = ($donnees['statistique'])->getScore();
                                    } else if ($donnees['periode'] == $seconde_mt) {
                                        $score_domicile += ($donnees['statistique'])->getScore();
                                        $score_2M_domicile = ($donnees['statistique'])->getScore();
                                        $id_equipe_domicile = $donnees['equipe']; // On récupère l'id de l'équipe à domicile
                                    }
                                } else if ($donnees['preponderance'] == $exterieur) {
                                    if ($donnees['periode'] == $premiere_mt) {
                                        $score_exterieur += ($donnees['statistique'])->getScore();
                                        $score_2M_exterieur = ($donnees['statistique'])->getScore();
                                    } else if ($donnees['periode'] == $seconde_mt) {
                                        $score_exterieur += ($donnees['statistique'])->getScore();
                                        $score_2M_exterieur = ($donnees['statistique'])->getScore();
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
                                // Gestion des 2 équipes se marquent ou pas
                                if ($score_domicile > 0 and $score_exterieur > 0) {
                                    $classement[$championnat->getNom()][$code][Utilitaire::EM2] = 1;
                                    $classement[$championnat->getNom()][$code][Utilitaire::EM1] = 0;
                                } else {
                                    $classement[$championnat->getNom()][$code][Utilitaire::EM1] = 1;
                                    $classement[$championnat->getNom()][$code][Utilitaire::EM2] = 0;
                                }

                                // Gestion du nul dans chaque mi-temps ou pas 
                                if ($score_PM_domicile == $score_PM_exterieur) {
                                    $but_mi_temps[$championnat->getNom()][$code][Utilitaire::NPM] = 1;
                                    $but_mi_temps[$championnat->getNom()][$code][Utilitaire::BPM] = 0;
                                } else {
                                    $but_mi_temps[$championnat->getNom()][$code][Utilitaire::BPM] = 1;
                                    $but_mi_temps[$championnat->getNom()][$code][Utilitaire::NPM] = 0;
                                }

                                if ($score_2M_domicile == $score_2M_exterieur) {
                                    $but_mi_temps[$championnat->getNom()][$code][Utilitaire::N2M] = 1;
                                    $but_mi_temps[$championnat->getNom()][$code][Utilitaire::B2M] = 0;
                                } else {
                                    $but_mi_temps[$championnat->getNom()][$code][Utilitaire::B2M] = 1;
                                    $but_mi_temps[$championnat->getNom()][$code][Utilitaire::N2M] = 0;
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

                                    // Gestion du nul dans chaque mi-temps ou pas 
                                    if ($score_PM_domicile == $score_PM_exterieur) {
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::NPM] = 1;
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::BPM] = 0;
                                    } else {
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::BPM] = 1;
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::NPM] = 0;
                                    }

                                    if ($score_2M_domicile == $score_2M_exterieur) {
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::N2M] = 1;
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::B2M] = 0;
                                    } else {
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::B2M] = 1;
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::N2M] = 0;
                                    }
                                } else {
                                    // Le code existe on l'incrémente
                                    if ($score_domicile > 0 and $score_exterieur > 0) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::EM2] += 1;
                                    } else {
                                        $classement[$championnat->getNom()][$code][Utilitaire::EM1] += 1;
                                    }

                                    // Gestion du nul dans chaque mi-temps ou pas 
                                    if ($score_PM_domicile == $score_PM_exterieur) {
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::NPM] += 1;
                                    } else {
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::BPM] += 1;
                                    }

                                    if ($score_2M_domicile == $score_2M_exterieur) {
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::N2M] += 1;
                                    } else {
                                        $but_mi_temps[$championnat->getNom()][$code][Utilitaire::B2M] += 1;
                                    }
                                }
                            }

                            // On effectue la sauvegarde des données pour l'analyse des rencontres à venir
                            if (
                                24 == $calendrier['id'] and //$data_2EM_O_N['id_calendrier_precedent']
                                1 == $saison->getId() and //$data_2EM_O_N['id_saison']
                                1 == $championnat->getId() //$data_2EM_O_N['id_championnat']
                            ) {
                                // Gestion des 2 équipes marquent ou pas----------------------
                                // On effectue les calculs sur le classement
                                foreach ($classement[$championnat->getNom()] as $key => $classe) {
                                    $classement[$championnat->getNom()][$key][Utilitaire::TR] = $classement[$championnat->getNom()][$key][Utilitaire::EM1] + $classement[$championnat->getNom()][$key][Utilitaire::EM2];
                                    $classement[$championnat->getNom()][$key][Utilitaire::PEM1] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::EM1] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                                    $classement[$championnat->getNom()][$key][Utilitaire::PEM2] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::EM2] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                                    $classement[$championnat->getNom()][$key][Utilitaire::P_PARI] = ($classement[$championnat->getNom()][$key][Utilitaire::PEM1] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::PEM2] >= Utilitaire::PARI) ? true : false;
                                }

                                // On sauvegarde les données des rencontres
                                if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI]) {
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['club']['domicile'] = $id_equipe_domicile;
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['club']['exterieur'] = $id_equipe_exterieur;
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['classement'] = $code;
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['rang']['domicile'] = $rang_domicile;
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['rang']['exterieur'] = $rang_exterieur;
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['calendrier'] = 25; //$data_2EM_O_N['id_calendrier'];
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['rencontre'] = $repository->findRencontreBySaisonByClubByCalendrier(1, 25, $id_equipe_domicile); //$data_2EM_O_N['id_saison'] , $data_2EM_O_N['id_calendrier']
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage'] = ($classement[$championnat->getNom()][$code][Utilitaire::PEM1] > $classement[$championnat->getNom()][$code][Utilitaire::PEM2]) ? $classement[$championnat->getNom()][$code][Utilitaire::PEM1] : $classement[$championnat->getNom()][$code][Utilitaire::PEM2];
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI] = ($classement[$championnat->getNom()][$code][Utilitaire::PEM1] > $classement[$championnat->getNom()][$code][Utilitaire::PEM2]) ? Utilitaire::EM1 : Utilitaire::EM2;
                                    $cpte++;
                                }


                                // Gestion du nul dans chaque mi-temps ou pas----------------------
                                // On effectue les calculs sur le but_mi_temps
                                foreach ($but_mi_temps[$championnat->getNom()] as $key => $classe) {                                 
                                    $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR] = $but_mi_temps[$championnat->getNom()][$key][Utilitaire::NPM] + $but_mi_temps[$championnat->getNom()][$key][Utilitaire::BPM];
                                    $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PNPM] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::NPM] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                                    $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PBPM] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::BPM] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                                    $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PN2M] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::N2M] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                                    $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PB2M] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::B2M] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                                    $but_mi_temps[$championnat->getNom()][$key][Utilitaire::P_PARI_PM] = ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::PNPM] >= Utilitaire::PARI or $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PBPM] >= Utilitaire::PARI) ? true : false;
                                    $but_mi_temps[$championnat->getNom()][$key][Utilitaire::P_PARI_2M] = ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::PN2M] >= Utilitaire::PARI or $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PB2M] >= Utilitaire::PARI) ? true : false;
                                }

                                // On sauvegarde les données des rencontres
                                if ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::P_PARI_PM] or $but_mi_temps[$championnat->getNom()][$code][Utilitaire::P_PARI_2M]) {
                                    $donnees_but_mi_temps[$championnat->getNom()][$cptn]['club']['domicile'] = $id_equipe_domicile;
                                    $donnees_but_mi_temps[$championnat->getNom()][$cptn]['club']['exterieur'] = $id_equipe_exterieur;
                                    $donnees_but_mi_temps[$championnat->getNom()][$cptn]['classement'] = $code;
                                    $donnees_but_mi_temps[$championnat->getNom()][$cptn]['rang']['domicile'] = $rang_domicile;
                                    $donnees_but_mi_temps[$championnat->getNom()][$cptn]['rang']['exterieur'] = $rang_exterieur;
                                    $donnees_but_mi_temps[$championnat->getNom()][$cptn]['calendrier'] = 25; //$data_2EM_O_N['id_calendrier'];
                                    $donnees_but_mi_temps[$championnat->getNom()][$cptn]['rencontre'] = $repository->findRencontreBySaisonByClubByCalendrier(1, 25, $id_equipe_domicile); //$data_2EM_O_N['id_saison'] , $data_2EM_O_N['id_calendrier']
                                    
                                    if($but_mi_temps[$championnat->getNom()][$code][Utilitaire::P_PARI_PM])
                                    {
                                        $donnees_but_mi_temps[$championnat->getNom()][$cptn]['pourcentage_PM'] = ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::NPM] > $but_mi_temps[$championnat->getNom()][$code][Utilitaire::BPM]) ? $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PNPM] : $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PBPM];
                                        $donnees_but_mi_temps[$championnat->getNom()][$cptn][Utilitaire::P_PARI_PM] = ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::PNPM] > $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PBPM]) ? Utilitaire::NPM : Utilitaire::BPM;
                                    }
                                    
                                    if($but_mi_temps[$championnat->getNom()][$code][Utilitaire::P_PARI_2M])
                                    {
                                        $donnees_but_mi_temps[$championnat->getNom()][$cptn]['pourcentage_2M'] = ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::N2M] > $but_mi_temps[$championnat->getNom()][$code][Utilitaire::B2M]) ? $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PN2M] : $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PB2M];
                                        $donnees_but_mi_temps[$championnat->getNom()][$cptn][Utilitaire::P_PARI_2M] = ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::PN2M] > $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PB2M]) ? Utilitaire::N2M : Utilitaire::B2M;
                                    }
                                    $cptn++;
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
                if (!empty($classement[$championnat->getNom()])) {
                    // Gestion des deux équipes se marquent ou pas
                    foreach ($classement[$championnat->getNom()] as $key => $classe) {
                        $classement[$championnat->getNom()][$key][Utilitaire::TR] = $classement[$championnat->getNom()][$key][Utilitaire::EM1] + $classement[$championnat->getNom()][$key][Utilitaire::EM2];
                        $classement[$championnat->getNom()][$key][Utilitaire::PEM1] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::EM1] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::PEM2] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::EM2] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI] = ($classement[$championnat->getNom()][$key][Utilitaire::PEM1] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::PEM2] >= Utilitaire::PARI) ? true : false;
                    }

                    // Gestion du nul dans chaque mi-temps ou pas
                    foreach ($but_mi_temps[$championnat->getNom()] as $key => $classe) {
                        $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR] = $but_mi_temps[$championnat->getNom()][$key][Utilitaire::NPM] + $but_mi_temps[$championnat->getNom()][$key][Utilitaire::BPM];
                        $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PNPM] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::NPM] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PBPM] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::BPM] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PN2M] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::N2M] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PB2M] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::B2M] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $but_mi_temps[$championnat->getNom()][$key][Utilitaire::P_PARI_PM] = ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::PNPM] >= Utilitaire::PARI or $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PBPM] >= Utilitaire::PARI) ? true : false;
                        $but_mi_temps[$championnat->getNom()][$key][Utilitaire::P_PARI_2M] = ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::PN2M] >= Utilitaire::PARI or $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PB2M] >= Utilitaire::PARI) ? true : false;
                    }
                }
            }
        }

        return [
            "classement" => $classement,
            "deux_equipe_marquent" => $donnees_2EM_O_N,
            "but_mi_temps" => $but_mi_temps,
            "nul_chaque_mi_temps" => $donnees_but_mi_temps,
        ];
    }

    private function EM2_Oui_Non(mixed $repository, array $donnees): array
    {
        $retour = [];
        $cpt = 0;
        foreach ($donnees as $key => $data) {
            foreach ($data as $stat) {
                $equipe_domicile = $repository->equipe($stat['club']['domicile']);
                $equipe_exterieur = $repository->equipe($stat['club']['exterieur']);
                $rencontre = $repository->rencontre($stat['rencontre']);
                $calendrier = $repository->getCalendrier($stat['calendrier']);
                $retour[$key][$cpt]['club']['domicile'] = ucfirst(string: $equipe_domicile->getNom());
                $retour[$key][$cpt]['club']['exterieur'] = ucfirst(string: $equipe_exterieur->getNom());
                $retour[$key][$cpt]['logo']['domicile'] = $equipe_domicile->getLogo();
                $retour[$key][$cpt]['logo']['exterieur'] = $equipe_exterieur->getLogo();
                $rang_domicile = (((int) $stat['rang']['domicile']) > 1) ? 'e' : 'er';
                $rang_exterieur = (((int) $stat['rang']['exterieur']) > 1) ? 'e' : 'er';
                $retour[$key][$cpt]['rang']['domicile'] = $stat['rang']['domicile'] . $rang_domicile;
                $retour[$key][$cpt]['rang']['exterieur'] = $stat['rang']['exterieur'] . $rang_exterieur;
                $retour[$key][$cpt]['rencontre'] = $rencontre->getDescription();
                $retour[$key][$cpt]['journee'] = $calendrier->getJournee()->getDescriptionSimple();
                $retour[$key][$cpt]['pourcentage'] = $stat['pourcentage'];
                $retour[$key][$cpt][Utilitaire::P_PARI] = $stat[Utilitaire::P_PARI];
                $retour[$key][$cpt]['classement'] = $stat['classement'];

                $cpt++;
            }
        }

        return $retour;
    }

    private function nul_chaque_mi_temps(mixed $repository, array $donnees): array
    {
        $retour = [];
        $cpt = 0;
        foreach ($donnees as $key => $data) {
            foreach ($data as $stat) {
                $equipe_domicile = $repository->equipe($stat['club']['domicile']);
                $equipe_exterieur = $repository->equipe($stat['club']['exterieur']);
                $rencontre = $repository->rencontre($stat['rencontre']);
                $calendrier = $repository->getCalendrier($stat['calendrier']);
                $retour[$key][$cpt]['club']['domicile'] = ucfirst(string: $equipe_domicile->getNom());
                $retour[$key][$cpt]['club']['exterieur'] = ucfirst(string: $equipe_exterieur->getNom());
                $retour[$key][$cpt]['logo']['domicile'] = $equipe_domicile->getLogo();
                $retour[$key][$cpt]['logo']['exterieur'] = $equipe_exterieur->getLogo();
                $rang_domicile = (((int) $stat['rang']['domicile']) > 1) ? 'e' : 'er';
                $rang_exterieur = (((int) $stat['rang']['exterieur']) > 1) ? 'e' : 'er';
                $retour[$key][$cpt]['rang']['domicile'] = $stat['rang']['domicile'] . $rang_domicile;
                $retour[$key][$cpt]['rang']['exterieur'] = $stat['rang']['exterieur'] . $rang_exterieur;
                $retour[$key][$cpt]['rencontre'] = $rencontre->getDescription();
                $retour[$key][$cpt]['journee'] = $calendrier->getJournee()->getDescriptionSimple();
                $retour[$key][$cpt]['classement'] = $stat['classement'];

                if(!empty($stat['pourcentage_PM']))
                {
                    $retour[$key][$cpt]['pourcentage_PM'] = $stat['pourcentage_PM'];
                    $retour[$key][$cpt][Utilitaire::P_PARI_PM] = $stat[Utilitaire::P_PARI_PM];
                }

                if(!empty($stat['pourcentage_2M']))
                {
                    $retour[$key][$cpt]['pourcentage_2M'] = $stat['pourcentage_2M'];
                    $retour[$key][$cpt][Utilitaire::P_PARI_2M] = $stat[Utilitaire::P_PARI_2M];
                }

                $cpt++;
            }
        }

        return $retour;
    }
}
