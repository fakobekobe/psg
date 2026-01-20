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

        $hors_jeu = $stat["hors_jeu"];
        $donnees_hors_jeu = $stat["donnees_hors_jeu"];
        $donnees_hors_jeu = $this->hors_jeu(repository: $this->statistique_repository, donnees: $donnees_hors_jeu);

        return $this->render(
            view: 'tableaudebord/index.html.twig',
            parameters: [
                'classements' => $stat_classement,
                'em2_oui_non' => $stat_2EM,
                "but_mi_temps" => $but_mi_temps,
                "nul_chaque_mi_temps" => $nul_chaque_mi_temps,
                "hors_jeu" => $hors_jeu,
                "donnees_hors_jeu" => $donnees_hors_jeu,
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

        $hors_jeu_PM_domicile = 0;
        $hors_jeu_2M_domicile = 0;
        $hors_jeu_PM_exterieur = 0;
        $hors_jeu_2M_exterieur = 0;
        $hors_jeu = [];
        $donnees_hors_jeu = [];

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
                $cpth = 0;

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
                                        $hors_jeu_PM_domicile = ($donnees['statistique'])->getHorsJeu();
                                    } else if ($donnees['periode'] == $seconde_mt) {
                                        $score_domicile += ($donnees['statistique'])->getScore();
                                        $score_2M_domicile = ($donnees['statistique'])->getScore();
                                        $hors_jeu_2M_domicile = ($donnees['statistique'])->getHorsJeu();
                                        $id_equipe_domicile = $donnees['equipe']; // On récupère l'id de l'équipe à domicile
                                    }
                                } else if ($donnees['preponderance'] == $exterieur) {
                                    if ($donnees['periode'] == $premiere_mt) {
                                        $score_exterieur += ($donnees['statistique'])->getScore();
                                        $score_PM_exterieur = ($donnees['statistique'])->getScore();
                                        $hors_jeu_PM_exterieur = ($donnees['statistique'])->getHorsJeu();
                                    } else if ($donnees['periode'] == $seconde_mt) {
                                        $score_exterieur += ($donnees['statistique'])->getScore();
                                        $score_2M_exterieur = ($donnees['statistique'])->getScore();
                                        $hors_jeu_2M_exterieur = ($donnees['statistique'])->getHorsJeu();
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
                                // Gestion des 2 équipes se marquent ou pas --------------------
                                if ($score_PM_domicile > 0 and $score_PM_exterieur > 0) {

                                    if ($score_PM_domicile > $score_PM_exterieur) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 0;
                                    } else if ($score_PM_domicile < $score_PM_exterieur) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 0;
                                    } else {
                                        $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 0;
                                    }
                                } else if ($score_PM_domicile > 0 or $score_PM_exterieur > 0) {
                                    if ($score_PM_domicile > 0) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 0;
                                    } else {
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 0;
                                    }
                                } else if ($score_PM_domicile == 0 and $score_PM_exterieur == 0) {
                                    $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 1;
                                    $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 0;
                                    $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 0;
                                    $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 0;
                                    $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 0;
                                    $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 0;
                                    $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 1;
                                    $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 0;
                                }

                                if ($score_2M_domicile > 0 and $score_2M_exterieur > 0) {

                                    if ($score_2M_domicile > $score_2M_exterieur) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 0;
                                    } else if ($score_2M_domicile < $score_2M_exterieur) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 0;
                                    } else {
                                        $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 0;
                                    }
                                } else if ($score_2M_domicile > 0 or $score_2M_exterieur > 0) {
                                    if ($score_2M_domicile > 0) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 0;
                                    } else {
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 0;
                                    }
                                } else if ($score_2M_domicile == 0 and $score_2M_exterieur == 0) {
                                    $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 1;
                                    $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 0;
                                    $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 0;
                                    $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 0;
                                    $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 0;
                                    $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 0;
                                    $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 1;
                                    $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 0;
                                }

                                // Gestion du nul dans chaque mi-temps ou pas ----------------------
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

                                // Gestion du hors jeu dans chaque mi-temps 
                                if ($hors_jeu_PM_domicile > $hors_jeu_PM_exterieur) {
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::D1] = 1;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::E1] = 0;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::N1] = 0;
                                } else if ($hors_jeu_PM_domicile < $hors_jeu_PM_exterieur) {
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::E1] = 1;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::D1] = 0;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::N1] = 0;
                                } else {
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::N1] = 1;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::E1] = 0;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::D1] = 0;
                                }

                                if ($hors_jeu_2M_domicile > $hors_jeu_2M_exterieur) {
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::D2] = 1;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::E2] = 0;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::N2] = 0;
                                } else if ($hors_jeu_2M_domicile < $hors_jeu_2M_exterieur) {
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::E2] = 1;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::D2] = 0;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::N2] = 0;
                                } else {
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::N2] = 1;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::E2] = 0;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::D2] = 0;
                                }

                                if (($hors_jeu_PM_domicile + $hors_jeu_2M_domicile) > ($hors_jeu_PM_exterieur + $hors_jeu_2M_exterieur)) {
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::D3] = 1;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::E3] = 0;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::N3] = 0;
                                } else if (($hors_jeu_PM_domicile + $hors_jeu_2M_domicile) < ($hors_jeu_PM_exterieur + $hors_jeu_2M_exterieur)) {
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::E3] = 1;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::D3] = 0;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::N3] = 0;
                                } else if (($hors_jeu_PM_domicile + $hors_jeu_2M_domicile) == ($hors_jeu_PM_exterieur + $hors_jeu_2M_exterieur)) {
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::N3] = 1;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::E3] = 0;
                                    $hors_jeu[$championnat->getNom()][$code][Utilitaire::D3] = 0;
                                }
                            } else {
                                if (!array_key_exists(key: $code, array: $classement[$championnat->getNom()])) {
                                    // Le code n'existe pas on l'ajoute pour la premier fois  -------
                                    // Gestion des deux équipes de marquent ou pas                  
                                    if ($score_PM_domicile > 0 and $score_PM_exterieur > 0) {

                                        if ($score_PM_domicile > $score_PM_exterieur) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 0;
                                        } else if ($score_PM_domicile < $score_PM_exterieur) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 0;
                                        } else {
                                            $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 0;
                                        }
                                    } else if ($score_PM_domicile > 0 or $score_PM_exterieur > 0) {
                                        if ($score_PM_domicile > 0) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 0;
                                        } else {
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 0;
                                        }
                                    } else if ($score_PM_domicile == 0 and $score_PM_exterieur == 0) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB1] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P1M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P2M] = 0;
                                    }

                                    if ($score_2M_domicile > 0 and $score_2M_exterieur > 0) {

                                        if ($score_2M_domicile > $score_2M_exterieur) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 0;
                                        } else if ($score_2M_domicile < $score_2M_exterieur) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 0;
                                        } else {
                                            $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 0;
                                        }
                                    } else if ($score_2M_domicile > 0 or $score_2M_exterieur > 0) {
                                        if ($score_2M_domicile > 0) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 0;
                                        } else {
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 0;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 0;
                                        }
                                    } else if ($score_2M_domicile == 0 and $score_2M_exterieur == 0) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB2] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] = 0;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1M] = 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D2M] = 0;
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

                                    // Gestion du hors jeu dans chaque mi-temps 
                                    if ($hors_jeu_PM_domicile > $hors_jeu_PM_exterieur) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D1] = 1;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E1] = 0;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N1] = 0;
                                    } else if ($hors_jeu_PM_domicile < $hors_jeu_PM_exterieur) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E1] = 1;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D1] = 0;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N1] = 0;
                                    } else {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N1] = 1;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E1] = 0;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D1] = 0;
                                    }

                                    if ($hors_jeu_2M_domicile > $hors_jeu_2M_exterieur) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D2] = 1;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E2] = 0;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N2] = 0;
                                    } else if ($hors_jeu_2M_domicile < $hors_jeu_2M_exterieur) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E2] = 1;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D2] = 0;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N2] = 0;
                                    } else {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N2] = 1;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E2] = 0;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D2] = 0;
                                    }

                                    if (($hors_jeu_PM_domicile + $hors_jeu_2M_domicile) > ($hors_jeu_PM_exterieur + $hors_jeu_2M_exterieur)) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D3] = 1;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E3] = 0;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N3] = 0;
                                    } else if (($hors_jeu_PM_domicile + $hors_jeu_2M_domicile) < ($hors_jeu_PM_exterieur + $hors_jeu_2M_exterieur)) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E3] = 1;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D3] = 0;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N3] = 0;
                                    } else if (($hors_jeu_PM_domicile + $hors_jeu_2M_domicile) == ($hors_jeu_PM_exterieur + $hors_jeu_2M_exterieur)) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N3] = 1;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E3] = 0;
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D3] = 0;
                                    }
                                } else {
                                    // Le code existe on l'incrémente --------------------
                                    // Gestion des deux équipes se marquent ou pas
                                    if ($score_PM_domicile > 0 and $score_PM_exterieur > 0) {

                                        if ($score_PM_domicile > $score_PM_exterieur) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_2M] += 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P2M] += 1;
                                        } else if ($score_PM_domicile < $score_PM_exterieur) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_2M] += 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P2M] += 1;
                                        } else {
                                            $classement[$championnat->getNom()][$code][Utilitaire::N1_2M] += 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P2M] += 1;
                                        }
                                    } else if ($score_PM_domicile > 0 or $score_PM_exterieur > 0) {
                                        if ($score_PM_domicile > 0) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1_1M] += 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P1M] += 1;
                                        } else {
                                            $classement[$championnat->getNom()][$code][Utilitaire::E1_1M] += 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::P1M] += 1;
                                        }
                                    } else if ($score_PM_domicile == 0 and $score_PM_exterieur == 0) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB1] += 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::P1M] += 1;
                                    }

                                    if ($score_2M_domicile > 0 and $score_2M_exterieur > 0) {

                                        if ($score_2M_domicile > $score_2M_exterieur) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_2M] += 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2M] += 1;
                                        } else if ($score_2M_domicile < $score_2M_exterieur) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_2M] += 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2M] += 1;
                                        } else {
                                            $classement[$championnat->getNom()][$code][Utilitaire::N2_2M] += 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2M] += 1;
                                        }
                                    } else if ($score_2M_domicile > 0 or $score_2M_exterieur > 0) {
                                        if ($score_2M_domicile > 0) {
                                            $classement[$championnat->getNom()][$code][Utilitaire::D2_1M] += 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1M] += 1;
                                        } else {
                                            $classement[$championnat->getNom()][$code][Utilitaire::E2_1M] += 1;
                                            $classement[$championnat->getNom()][$code][Utilitaire::D1M] += 1;
                                        }
                                    } else if ($score_2M_domicile == 0 and $score_2M_exterieur == 0) {
                                        $classement[$championnat->getNom()][$code][Utilitaire::PB2] += 1;
                                        $classement[$championnat->getNom()][$code][Utilitaire::D1M] += 1;
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

                                    // Gestion du hors jeu dans chaque mi-temps 
                                    if ($hors_jeu_PM_domicile > $hors_jeu_PM_exterieur) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D1] += 1;
                                    } else if ($hors_jeu_PM_domicile < $hors_jeu_PM_exterieur) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E1] += 1;
                                    } else {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N1] += 1;
                                    }

                                    if ($hors_jeu_2M_domicile > $hors_jeu_2M_exterieur) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D2] += 1;
                                    } else if ($hors_jeu_2M_domicile < $hors_jeu_2M_exterieur) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E2] += 1;
                                    } else {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N2] += 1;
                                    }

                                    if (($hors_jeu_PM_domicile + $hors_jeu_2M_domicile) > ($hors_jeu_PM_exterieur + $hors_jeu_2M_exterieur)) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::D3] += 1;
                                    } else if (($hors_jeu_PM_domicile + $hors_jeu_2M_domicile) < ($hors_jeu_PM_exterieur + $hors_jeu_2M_exterieur)) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::E3] += 1;
                                    } else if (($hors_jeu_PM_domicile + $hors_jeu_2M_domicile) == ($hors_jeu_PM_exterieur + $hors_jeu_2M_exterieur)) {
                                        $hors_jeu[$championnat->getNom()][$code][Utilitaire::N3] += 1;
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

                    // On effectue le traitement des match à venir
                    if (
                        $data_2EM_O_N['id_calendrier_precedent'] == $calendrier['id'] and 
                        $data_2EM_O_N['id_saison'] == $saison->getId() and 
                        $data_2EM_O_N['id_championnat'] == $championnat->getId() 
                    ) {
                        // Gestion des 2 équipes marquent ou pas : On effectue les calculs sur le classement
                        foreach ($classement[$championnat->getNom()] as $key => $classe) {
                            $classement[$championnat->getNom()][$key][Utilitaire::TR] = $classement[$championnat->getNom()][$key][Utilitaire::D1_2M] + 
                                $classement[$championnat->getNom()][$key][Utilitaire::E1_2M] + 
                                $classement[$championnat->getNom()][$key][Utilitaire::N1_2M] +
                                $classement[$championnat->getNom()][$key][Utilitaire::D1_1M] + 
                                $classement[$championnat->getNom()][$key][Utilitaire::E1_1M] +
                                $classement[$championnat->getNom()][$key][Utilitaire::PB1]
                                ;
                            $classement[$championnat->getNom()][$key][Utilitaire::P_D1_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D1_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_E1_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::E1_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_N1_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::N1_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_D1_1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D1_1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_E1_1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::E1_1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PB1] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::PB1] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PARI1_2M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D1_2M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_E1_2M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_N1_2M] >= Utilitaire::PARI) ? true : false;
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PARI1_1M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D1_1M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_E1_1M] >= Utilitaire::PARI) ? true : false;
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PARI1_PB] = ($classement[$championnat->getNom()][$key][Utilitaire::P_PB1] >= Utilitaire::PARI) ? true : false;
                            $classement[$championnat->getNom()][$key][Utilitaire::P_P2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::P2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_P1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::P1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PARI1_P2M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_P2M] >= Utilitaire::PARI) ? true : false;
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PARI1_P1M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_P1M] >= Utilitaire::PARI) ? true : false;
                            

                            $classement[$championnat->getNom()][$key][Utilitaire::P_D2_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D2_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_E2_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::E2_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_N2_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::N2_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_D2_1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D2_1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_E2_1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::E2_1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PB2] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::PB2] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PARI2_2M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D2_2M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_E2_2M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_N2_2M] >= Utilitaire::PARI) ? true : false;
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PARI2_1M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D2_1M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_E2_1M] >= Utilitaire::PARI) ? true : false;
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PARI2_PB] = ($classement[$championnat->getNom()][$key][Utilitaire::P_PB2] >= Utilitaire::PARI) ? true : false;
                            $classement[$championnat->getNom()][$key][Utilitaire::P_D2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_D1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PARI2_D2M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D2M] >= Utilitaire::PARI) ? true : false;
                            $classement[$championnat->getNom()][$key][Utilitaire::P_PARI2_D1M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D1M] >= Utilitaire::PARI) ? true : false;
                            
                        }

                        // Gestion du nul dans chaque mi-temps ou pas : On effectue les calculs sur le but_mi_temps
                        foreach ($but_mi_temps[$championnat->getNom()] as $key => $classe) {
                            $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR] = $but_mi_temps[$championnat->getNom()][$key][Utilitaire::NPM] + $but_mi_temps[$championnat->getNom()][$key][Utilitaire::BPM];
                            $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PNPM] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::NPM] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PBPM] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::BPM] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PN2M] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::N2M] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PB2M] = round(num: ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::B2M] / $but_mi_temps[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $but_mi_temps[$championnat->getNom()][$key][Utilitaire::P_PARI_PM] = ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::PNPM] >= Utilitaire::PARI or $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PBPM] >= Utilitaire::PARI) ? true : false;
                            $but_mi_temps[$championnat->getNom()][$key][Utilitaire::P_PARI_2M] = ($but_mi_temps[$championnat->getNom()][$key][Utilitaire::PN2M] >= Utilitaire::PARI or $but_mi_temps[$championnat->getNom()][$key][Utilitaire::PB2M] >= Utilitaire::PARI) ? true : false;
                        }

                        // Gestion du hors-jeu : On effectue les calculs sur le hors-jeu
                        foreach ($hors_jeu[$championnat->getNom()] as $key => $classe) {
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR] = $hors_jeu[$championnat->getNom()][$key][Utilitaire::D1] + $hors_jeu[$championnat->getNom()][$key][Utilitaire::E1] + $hors_jeu[$championnat->getNom()][$key][Utilitaire::N1];
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::PD1] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::D1] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE1] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::E1] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN1] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::N1] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::PD2] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::D2] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE2] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::E2] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN2] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::N2] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::PD3] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::D3] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE3] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::E3] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN3] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::N3] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::P_PARI1] = ($hors_jeu[$championnat->getNom()][$key][Utilitaire::PD1] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE1] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN1] >= Utilitaire::PARI) ? true : false;
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::P_PARI2] = ($hors_jeu[$championnat->getNom()][$key][Utilitaire::PD2] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE2] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN2] >= Utilitaire::PARI) ? true : false;
                            $hors_jeu[$championnat->getNom()][$key][Utilitaire::P_PARI3] = ($hors_jeu[$championnat->getNom()][$key][Utilitaire::PD3] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE3] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN3] >= Utilitaire::PARI) ? true : false;
                        }

                        // On récupère les infos des équipes du match à venir -----
                        // On récupère les rencontres
                        $s_rencontres = $repository->rencontres(id_saison: $data_2EM_O_N['id_saison'], id_calendrier: $data_2EM_O_N['id_calendrier']);

                        // On parcours les rencontres
                        foreach ($s_rencontres as $s_rencontre) {
                            // On récupère les matchs en cours
                            $s_matchs = $repository->getListeMatchByRencontre(id_rencontre: $s_rencontre->getId());

                            // On parcours les matchs 
                            foreach ($s_matchs as $s_match) {
                                // On récupère les équipes
                                if ($s_match->getPreponderance()->getId() == $domicile) {
                                    $id_equipe_domicile = $s_match->getEquipeSaison()->getEquipe()->getId();
                                } else if ($s_match->getPreponderance()->getId() == $exterieur) {
                                    $id_equipe_exterieur = $s_match->getEquipeSaison()->getEquipe()->getId();
                                }
                            }

                            // On récupère le code du classement
                            for ($i = 0; $i < count(value: $club); $i++) {
                                if ($club[$i]['id'] == $id_equipe_domicile) {
                                    $rang_domicile = $i + 1;
                                } else if ($club[$i]['id'] == $id_equipe_exterieur) {
                                    $rang_exterieur = $i + 1;
                                }
                            }

                            $code = Utilitaire::categorie_classement(rang_domicile: $rang_domicile, rang_exterieur: $rang_exterieur);

                            // Gestion des 2 équipes marquent ou pas : On sauvegarde les données des rencontres
                            if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI1_2M] or 
                                $classement[$championnat->getNom()][$code][Utilitaire::P_PARI1_1M] or 
                                $classement[$championnat->getNom()][$code][Utilitaire::P_PARI1_PB] or
                                $classement[$championnat->getNom()][$code][Utilitaire::P_PARI2_2M] or 
                                $classement[$championnat->getNom()][$code][Utilitaire::P_PARI2_1M] or 
                                $classement[$championnat->getNom()][$code][Utilitaire::P_PARI2_PB] or
                                $classement[$championnat->getNom()][$code][Utilitaire::P_PARI1_P2M] or
                                $classement[$championnat->getNom()][$code][Utilitaire::P_PARI1_P1M] or
                                $classement[$championnat->getNom()][$code][Utilitaire::P_PARI2_D2M] or
                                $classement[$championnat->getNom()][$code][Utilitaire::P_PARI2_D1M] 
                                ) {
                                $donnees_2EM_O_N[$championnat->getNom()][$cpte]['club']['domicile'] = $id_equipe_domicile;
                                $donnees_2EM_O_N[$championnat->getNom()][$cpte]['club']['exterieur'] = $id_equipe_exterieur;
                                $donnees_2EM_O_N[$championnat->getNom()][$cpte]['classement'] = $code;
                                $donnees_2EM_O_N[$championnat->getNom()][$cpte]['rang']['domicile'] = $rang_domicile;
                                $donnees_2EM_O_N[$championnat->getNom()][$cpte]['rang']['exterieur'] = $rang_exterieur;
                                $donnees_2EM_O_N[$championnat->getNom()][$cpte]['calendrier'] = $data_2EM_O_N['id_calendrier']; //$data_2EM_O_N['id_calendrier'];
                                $donnees_2EM_O_N[$championnat->getNom()][$cpte]['rencontre'] = $repository->findRencontreBySaisonByClubByCalendrier($data_2EM_O_N['id_saison'], $data_2EM_O_N['id_calendrier'], $id_equipe_domicile); //$data_2EM_O_N['id_saison'] , $data_2EM_O_N['id_calendrier']

                                if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI1_2M]) {
                                    if ($classement[$championnat->getNom()][$code][Utilitaire::D1_2M] > $classement[$championnat->getNom()][$code][Utilitaire::E1_2M]) {
                                        if ($classement[$championnat->getNom()][$code][Utilitaire::D1_2M] > $classement[$championnat->getNom()][$code][Utilitaire::N1_2M]) {
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_1_2M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_D1_2M]; 
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI1_2M] = Utilitaire::D1_2M;
                                        } else {
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_1_2M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_N1_2M]; 
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI1_2M] = Utilitaire::N1_2M;
                                        }
                                    } else {
                                        if ($classement[$championnat->getNom()][$code][Utilitaire::E1_2M] > $classement[$championnat->getNom()][$code][Utilitaire::N1_2M]) {
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_1_2M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_E1_2M]; 
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI1_2M] = Utilitaire::E1_2M;
                                        } else {
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_1_2M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_N1_2M];
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI1_2M] = Utilitaire::N1_2M;
                                        }
                                    }
                                }

                                if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI1_1M]) {
                                    if ($classement[$championnat->getNom()][$code][Utilitaire::D1_1M] > $classement[$championnat->getNom()][$code][Utilitaire::E1_1M]) {
                                        $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_1_1M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_D1_1M];
                                        $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI1_1M] = Utilitaire::D1_1M;
                                    } else {
                                        $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_1_1M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_E1_1M];
                                        $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI1_1M] = Utilitaire::E1_1M;
                                    }
                                }

                                if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI1_PB]) {
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_1_PB'] = $classement[$championnat->getNom()][$code][Utilitaire::P_PB1];
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI1_PB] = Utilitaire::PB1;
                                }

                                if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI2_2M]) {
                                    if ($classement[$championnat->getNom()][$code][Utilitaire::D2_2M] > $classement[$championnat->getNom()][$code][Utilitaire::E2_2M]) {
                                        if ($classement[$championnat->getNom()][$code][Utilitaire::D2_2M] > $classement[$championnat->getNom()][$code][Utilitaire::N2_2M]) {
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_2_2M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_D2_2M];
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI2_2M] = Utilitaire::D2_2M;
                                        } else {
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_2_2M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_N2_2M];
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI2_2M] = Utilitaire::N2_2M;
                                        }
                                    } else {
                                        if ($classement[$championnat->getNom()][$code][Utilitaire::E2_2M] > $classement[$championnat->getNom()][$code][Utilitaire::N2_2M]) {
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_2_2M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_E2_2M];
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI2_2M] = Utilitaire::E2_2M;
                                        } else {
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_2_2M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_N2_2M];
                                            $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI2_2M] = Utilitaire::N2_2M;
                                        }
                                    }
                                }

                                if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI2_1M]) {
                                    if ($classement[$championnat->getNom()][$code][Utilitaire::D2_1M] > $classement[$championnat->getNom()][$code][Utilitaire::E2_1M]) {
                                        $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_2_1M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_D2_1M];
                                        $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI2_1M] = Utilitaire::D2_1M;
                                    } else {
                                        $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_2_1M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_E2_1M];
                                        $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI2_1M] = Utilitaire::E2_1M;
                                    }
                                }

                                if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI2_PB]) {
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_2_PB'] = $classement[$championnat->getNom()][$code][Utilitaire::P_PB2];
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI2_PB] = Utilitaire::PB2;
                                }

                                if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI1_P2M]) {
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_1_P2M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_P2M];
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI1_P2M] = Utilitaire::P2M;
                                }

                                if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI1_P1M]) {
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_1_P1M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_P1M];
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI1_P1M] = Utilitaire::P1M;
                                }

                                if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI2_D2M]) {
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_2_D2M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_D2M];
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI2_D2M] = Utilitaire::D2M;
                                }

                                if ($classement[$championnat->getNom()][$code][Utilitaire::P_PARI2_D1M]) {
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte]['pourcentage_2_D1M'] = $classement[$championnat->getNom()][$code][Utilitaire::P_D1M];
                                    $donnees_2EM_O_N[$championnat->getNom()][$cpte][Utilitaire::P_PARI2_D1M] = Utilitaire::D1M;
                                }

                                $cpte++;
                            }


                            // Gestion du nul dans chaque mi-temps ou pas : On sauvegarde les données des rencontres
                            if ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::P_PARI_PM] or $but_mi_temps[$championnat->getNom()][$code][Utilitaire::P_PARI_2M]) {
                                $donnees_but_mi_temps[$championnat->getNom()][$cptn]['club']['domicile'] = $id_equipe_domicile;
                                $donnees_but_mi_temps[$championnat->getNom()][$cptn]['club']['exterieur'] = $id_equipe_exterieur;
                                $donnees_but_mi_temps[$championnat->getNom()][$cptn]['classement'] = $code;
                                $donnees_but_mi_temps[$championnat->getNom()][$cptn]['rang']['domicile'] = $rang_domicile;
                                $donnees_but_mi_temps[$championnat->getNom()][$cptn]['rang']['exterieur'] = $rang_exterieur;
                                $donnees_but_mi_temps[$championnat->getNom()][$cptn]['calendrier'] = $data_2EM_O_N['id_calendrier']; //$data_2EM_O_N['id_calendrier'];
                                $donnees_but_mi_temps[$championnat->getNom()][$cptn]['rencontre'] = $repository->findRencontreBySaisonByClubByCalendrier($data_2EM_O_N['id_saison'], $data_2EM_O_N['id_calendrier'], $id_equipe_domicile); //$data_2EM_O_N['id_saison'] , $data_2EM_O_N['id_calendrier']

                                if ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::P_PARI_PM]) {
                                    $donnees_but_mi_temps[$championnat->getNom()][$cptn]['pourcentage_PM'] = ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::NPM] > $but_mi_temps[$championnat->getNom()][$code][Utilitaire::BPM]) ? $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PNPM] : $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PBPM];
                                    $donnees_but_mi_temps[$championnat->getNom()][$cptn][Utilitaire::P_PARI_PM] = ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::PNPM] > $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PBPM]) ? Utilitaire::NPM : Utilitaire::BPM;
                                }

                                if ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::P_PARI_2M]) {
                                    $donnees_but_mi_temps[$championnat->getNom()][$cptn]['pourcentage_2M'] = ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::N2M] > $but_mi_temps[$championnat->getNom()][$code][Utilitaire::B2M]) ? $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PN2M] : $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PB2M];
                                    $donnees_but_mi_temps[$championnat->getNom()][$cptn][Utilitaire::P_PARI_2M] = ($but_mi_temps[$championnat->getNom()][$code][Utilitaire::PN2M] > $but_mi_temps[$championnat->getNom()][$code][Utilitaire::PB2M]) ? Utilitaire::N2M : Utilitaire::B2M;
                                }
                                $cptn++;
                            }

                            // Gestion du hors-jeu : On sauvegarde les données des rencontres
                            if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::P_PARI1] or 
                                $hors_jeu[$championnat->getNom()][$code][Utilitaire::P_PARI2] or 
                                $hors_jeu[$championnat->getNom()][$code][Utilitaire::P_PARI3]) {
                                $donnees_hors_jeu[$championnat->getNom()][$cpth]['club']['domicile'] = $id_equipe_domicile;
                                $donnees_hors_jeu[$championnat->getNom()][$cpth]['club']['exterieur'] = $id_equipe_exterieur;
                                $donnees_hors_jeu[$championnat->getNom()][$cpth]['classement'] = $code;
                                $donnees_hors_jeu[$championnat->getNom()][$cpth]['rang']['domicile'] = $rang_domicile;
                                $donnees_hors_jeu[$championnat->getNom()][$cpth]['rang']['exterieur'] = $rang_exterieur;
                                $donnees_hors_jeu[$championnat->getNom()][$cpth]['calendrier'] = $data_2EM_O_N['id_calendrier']; //$data_2EM_O_N['id_calendrier'];
                                $donnees_hors_jeu[$championnat->getNom()][$cpth]['rencontre'] = $repository->findRencontreBySaisonByClubByCalendrier($data_2EM_O_N['id_saison'], $data_2EM_O_N['id_calendrier'], $id_equipe_domicile); //$data_2EM_O_N['id_saison'] , $data_2EM_O_N['id_calendrier']

                                if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::P_PARI1]) {
                                    if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::D1] > $hors_jeu[$championnat->getNom()][$code][Utilitaire::E1]) {
                                        if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::D1] > $hors_jeu[$championnat->getNom()][$code][Utilitaire::N1]) {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_1'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PD1];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI1] = Utilitaire::D1;
                                        } else {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_1'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PN1];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI1] = Utilitaire::N1;
                                        }
                                    } else {
                                        if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::E1] > $hors_jeu[$championnat->getNom()][$code][Utilitaire::N1]) {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_1'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PE1];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI1] = Utilitaire::E1;
                                        } else {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_1'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PN1];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI1] = Utilitaire::N1;
                                        }
                                    }
                                }

                                if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::P_PARI2]) {
                                    if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::D2] > $hors_jeu[$championnat->getNom()][$code][Utilitaire::E2]) {
                                        if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::D2] > $hors_jeu[$championnat->getNom()][$code][Utilitaire::N2]) {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_2'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PD2];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI2] = Utilitaire::D2;
                                        } else {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_2'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PN2];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI2] = Utilitaire::N2;
                                        }
                                    } else {
                                        if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::E2] > $hors_jeu[$championnat->getNom()][$code][Utilitaire::N2]) {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_2'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PE2];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI2] = Utilitaire::E2;
                                        } else {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_2'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PN2];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI2] = Utilitaire::N2;
                                        }
                                    }
                                }

                                if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::P_PARI3]) {
                                    if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::D3] > $hors_jeu[$championnat->getNom()][$code][Utilitaire::E3]) {
                                        if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::D3] > $hors_jeu[$championnat->getNom()][$code][Utilitaire::N3]) {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_3'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PD3];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI3] = Utilitaire::D3;
                                        } else {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_3'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PN3];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI3] = Utilitaire::N3;
                                        }
                                    } else {
                                        if ($hors_jeu[$championnat->getNom()][$code][Utilitaire::E3] > $hors_jeu[$championnat->getNom()][$code][Utilitaire::N3]) {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_3'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PE3];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI3] = Utilitaire::E3;
                                        } else {
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth]['pourcentage_3'] = $hors_jeu[$championnat->getNom()][$code][Utilitaire::PN3];
                                            $donnees_hors_jeu[$championnat->getNom()][$cpth][Utilitaire::P_PARI3] = Utilitaire::N3;
                                        }
                                    }
                                }

                                $cpth++;
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
                        $classement[$championnat->getNom()][$key][Utilitaire::TR] = $classement[$championnat->getNom()][$key][Utilitaire::D1_2M] + 
                                $classement[$championnat->getNom()][$key][Utilitaire::E1_2M] + 
                                $classement[$championnat->getNom()][$key][Utilitaire::N1_2M] +
                                $classement[$championnat->getNom()][$key][Utilitaire::D1_1M] + 
                                $classement[$championnat->getNom()][$key][Utilitaire::E1_1M] +
                                $classement[$championnat->getNom()][$key][Utilitaire::PB1]
                                ;
                        $classement[$championnat->getNom()][$key][Utilitaire::P_D1_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D1_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_E1_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::E1_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_N1_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::N1_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_D1_1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D1_1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_E1_1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::E1_1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PB1] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::PB1] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI1_2M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D1_2M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_E1_2M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_N1_2M] >= Utilitaire::PARI) ? true : false;
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI1_1M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D1_1M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_E1_1M] >= Utilitaire::PARI) ? true : false;
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI1_PB] = ($classement[$championnat->getNom()][$key][Utilitaire::P_PB1] >= Utilitaire::PARI) ? true : false;
                        $classement[$championnat->getNom()][$key][Utilitaire::P_P2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::P2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_P1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::P1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI1_P2M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_P2M] >= Utilitaire::PARI) ? true : false;
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI1_P1M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_P1M] >= Utilitaire::PARI) ? true : false;
                            

                        $classement[$championnat->getNom()][$key][Utilitaire::P_D2_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D2_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_E2_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::E2_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_N2_2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::N2_2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_D2_1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D2_1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_E2_1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::E2_1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PB2] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::PB2] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI2_2M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D2_2M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_E2_2M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_N2_2M] >= Utilitaire::PARI) ? true : false;
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI2_1M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D2_1M] >= Utilitaire::PARI or $classement[$championnat->getNom()][$key][Utilitaire::P_E2_1M] >= Utilitaire::PARI) ? true : false;
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI2_PB] = ($classement[$championnat->getNom()][$key][Utilitaire::P_PB2] >= Utilitaire::PARI) ? true : false;
                        $classement[$championnat->getNom()][$key][Utilitaire::P_D2M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D2M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_D1M] = round(num: ($classement[$championnat->getNom()][$key][Utilitaire::D1M] / $classement[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI2_D2M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D2M] >= Utilitaire::PARI) ? true : false;
                        $classement[$championnat->getNom()][$key][Utilitaire::P_PARI2_D1M] = ($classement[$championnat->getNom()][$key][Utilitaire::P_D1M] >= Utilitaire::PARI) ? true : false;
                            
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

                    // Gestion du hors-jeu
                    foreach ($hors_jeu[$championnat->getNom()] as $key => $classe) {
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR] = $hors_jeu[$championnat->getNom()][$key][Utilitaire::D1] + $hors_jeu[$championnat->getNom()][$key][Utilitaire::E1] + $hors_jeu[$championnat->getNom()][$key][Utilitaire::N1];
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::PD1] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::D1] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE1] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::E1] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN1] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::N1] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::PD2] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::D2] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE2] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::E2] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN2] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::N2] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::PD3] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::D3] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE3] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::E3] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN3] = round(num: ($hors_jeu[$championnat->getNom()][$key][Utilitaire::N3] / $hors_jeu[$championnat->getNom()][$key][Utilitaire::TR]) * 100, precision: 0);
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::P_PARI1] = ($hors_jeu[$championnat->getNom()][$key][Utilitaire::PD1] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE1] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN1] >= Utilitaire::PARI) ? true : false;
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::P_PARI2] = ($hors_jeu[$championnat->getNom()][$key][Utilitaire::PD2] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE2] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN2] >= Utilitaire::PARI) ? true : false;
                        $hors_jeu[$championnat->getNom()][$key][Utilitaire::P_PARI3] = ($hors_jeu[$championnat->getNom()][$key][Utilitaire::PD3] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PE3] >= Utilitaire::PARI or $hors_jeu[$championnat->getNom()][$key][Utilitaire::PN3] >= Utilitaire::PARI) ? true : false;
                    }
                }
            }
        }

        return [
            "classement" => $classement,
            "deux_equipe_marquent" => $donnees_2EM_O_N,
            "but_mi_temps" => $but_mi_temps,
            "nul_chaque_mi_temps" => $donnees_but_mi_temps,
            "hors_jeu" => $hors_jeu,
            "donnees_hors_jeu" => $donnees_hors_jeu,
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
                $retour[$key][$cpt]['classement'] = $stat['classement'];

                if (!empty($stat['pourcentage_1_2M'])) {
                    $retour[$key][$cpt]['pourcentage_1_2M'] = $stat['pourcentage_1_2M'];
                    $retour[$key][$cpt][Utilitaire::P_PARI1_2M] = $stat[Utilitaire::P_PARI1_2M];
                }

                if (!empty($stat['pourcentage_2_2M'])) {
                    $retour[$key][$cpt]['pourcentage_2_2M'] = $stat['pourcentage_2_2M'];
                    $retour[$key][$cpt][Utilitaire::P_PARI2_2M] = $stat[Utilitaire::P_PARI2_2M];
                }

                if (!empty($stat['pourcentage_1_1M'])) {
                    $retour[$key][$cpt]['pourcentage_1_1M'] = $stat['pourcentage_1_1M'];
                    $retour[$key][$cpt][Utilitaire::P_PARI1_1M] = $stat[Utilitaire::P_PARI1_1M];
                }

                if (!empty($stat['pourcentage_2_1M'])) {
                    $retour[$key][$cpt]['pourcentage_2_1M'] = $stat['pourcentage_2_1M'];
                    $retour[$key][$cpt][Utilitaire::P_PARI2_1M] = $stat[Utilitaire::P_PARI2_1M];
                }

                if (!empty($stat['pourcentage_1_PB'])) {
                    $retour[$key][$cpt]['pourcentage_1_PB'] = $stat['pourcentage_1_PB'];
                    $retour[$key][$cpt][Utilitaire::P_PARI1_PB] = $stat[Utilitaire::P_PARI1_PB];
                }

                if (!empty($stat['pourcentage_2_PB'])) {
                    $retour[$key][$cpt]['pourcentage_2_PB'] = $stat['pourcentage_2_PB'];
                    $retour[$key][$cpt][Utilitaire::P_PARI2_PB] = $stat[Utilitaire::P_PARI2_PB];
                }

                if (!empty($stat['pourcentage_1_P2M'])) {
                    $retour[$key][$cpt]['pourcentage_1_P2M'] = $stat['pourcentage_1_P2M'];
                    $retour[$key][$cpt][Utilitaire::P_PARI1_P2M] = $stat[Utilitaire::P_PARI1_P2M];
                }

                if (!empty($stat['pourcentage_1_P1M'])) {
                    $retour[$key][$cpt]['pourcentage_1_P1M'] = $stat['pourcentage_1_P1M'];
                    $retour[$key][$cpt][Utilitaire::P_PARI1_P1M] = $stat[Utilitaire::P_PARI1_P1M];
                }

                if (!empty($stat['pourcentage_2_D2M'])) {
                    $retour[$key][$cpt]['pourcentage_2_D2M'] = $stat['pourcentage_2_D2M'];
                    $retour[$key][$cpt][Utilitaire::P_PARI2_D2M] = $stat[Utilitaire::P_PARI2_D2M];
                }

                if (!empty($stat['pourcentage_2_P1M'])) {
                    $retour[$key][$cpt]['pourcentage_2_D1M'] = $stat['pourcentage_2_D1M'];
                    $retour[$key][$cpt][Utilitaire::P_PARI2_D1M] = $stat[Utilitaire::P_PARI2_D1M];
                }

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

                if (!empty($stat['pourcentage_PM'])) {
                    $retour[$key][$cpt]['pourcentage_PM'] = $stat['pourcentage_PM'];
                    $retour[$key][$cpt][Utilitaire::P_PARI_PM] = $stat[Utilitaire::P_PARI_PM];
                }

                if (!empty($stat['pourcentage_2M'])) {
                    $retour[$key][$cpt]['pourcentage_2M'] = $stat['pourcentage_2M'];
                    $retour[$key][$cpt][Utilitaire::P_PARI_2M] = $stat[Utilitaire::P_PARI_2M];
                }

                $cpt++;
            }
        }

        return $retour;
    }

    private function hors_jeu(mixed $repository, array $donnees): array
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

                if (!empty($stat['pourcentage_1'])) {
                    $retour[$key][$cpt]['pourcentage_1'] = $stat['pourcentage_1'];
                    $retour[$key][$cpt][Utilitaire::P_PARI1] = $stat[Utilitaire::P_PARI1];
                }

                if (!empty($stat['pourcentage_2'])) {
                    $retour[$key][$cpt]['pourcentage_2'] = $stat['pourcentage_2'];
                    $retour[$key][$cpt][Utilitaire::P_PARI2] = $stat[Utilitaire::P_PARI2];
                }

                if (!empty($stat['pourcentage_3'])) {
                    $retour[$key][$cpt]['pourcentage_3'] = $stat['pourcentage_3'];
                    $retour[$key][$cpt][Utilitaire::P_PARI3] = $stat[Utilitaire::P_PARI3];
                }

                $cpt++;
            }
        }

        return $retour;
    }
}
