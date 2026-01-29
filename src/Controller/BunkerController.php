<?php

namespace App\Controller;

use App\Form\MatchDisputeType;
use App\Repository\StatistiqueRepository;
use App\Src\Traitement\Utilitaire\HtmlVue;
use App\Traitement\Utilitaire\Utilitaire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path:'/bunker')]
final class BunkerController extends AbstractController
{
    private const PREFIX_NAME = 'app_bunker';
    private const PAGE = '_bunker';
    private StatistiqueRepository $statistique_repository;

    public function __construct(
        private ManagerRegistry $registry,
        private FormFactoryInterface $form,
        )
    {
        $this->statistique_repository = new StatistiqueRepository(registry: $registry);
    }

    #[Route(path: '', name: self::PREFIX_NAME, methods: ['GET'])]
    #[IsGranted(attribute: "ajouter" . self::PAGE)]
    public function index(Request $request): Response
    {
        $form_analyse = $this->formulaire(
            $this->form,
            MatchDisputeType::class,
            'form_type',
        );
        return $this->render(view: 'bunker/index.html.twig', parameters:[
            'form_analyse' => $form_analyse->createView(),
        ]);             
    }

    #[Route(path: '/classement', name: self::PREFIX_NAME . '_classement', methods: ['POST'])]
    #[IsGranted(attribute: "ajouter" . self::PAGE)]
    public function classement(Request $request): Response
    {
        // Les variables
        $id_preponderance_domicile = 1;
        $id_preponderance_exterieur = 2;
        $id_periode_premiere_mt = 1;
        $id_periode_deuxieme_mt = 2;

        $reponse = $request->request->all()['match_dispute'];  
        $saison = (int) $reponse['saison']; 
        $championnat = (int) $reponse['championnat']; 
        $calendrier = (int) $reponse['calendrier'];         

        // On récupère les données
        $data = $this->statistique_repository->findStatistiqueBySaisonByChampionnatByCalendrier(
            id_saison: $saison,
            id_championnat: $championnat,
            id_calendrier: $calendrier
        );

        if(!$data) return new JsonResponse(data:['code'=> 'ECHEC', 'message'=> "Aucune données trouvée."]);

        // On récupère la journée selon le calendrier
        $journee = $this->statistique_repository->getJournee(id_calendrier: $calendrier);

        // On récupère les statistiques
        $stat_classement = $this->stat_classement(repository: $this->statistique_repository, 
                                    saison: $saison,
                                    championnat: $championnat,
                                    numero_journee: $journee->getNumero(),
                                    domicile: $id_preponderance_domicile,
                                    exterieur: $id_preponderance_exterieur,
                                    premiere_mt: $id_periode_premiere_mt,
                                    seconde_mt: $id_periode_deuxieme_mt,
                                    data: $data);

        $table = '<table>
        <tr class="bg-light">
            <th colspan="3" class="pl-2">Club</th>
            <th class="pl-2">J</th>
            <th class="pl-2">V</th>
            <th class="pl-2">N</th>
            <th class="pl-2">D</th>
            <th class="pl-2">TB</th>
            <th class="pl-2">BE</th>
            <th class="pl-2">DB</th>
            <th class="pl-2">Pts</th>
            <th class="pl-2 tsm">5 derniers</th>
        </tr>';
        // Traitement des données pour affichage
        for($i = 0; $i < count(value: $stat_classement); $i++)
        {
            // Couleur par tranche
            $couleur = "";
            switch(true)
            {
                case in_array(needle: $i, haystack: range(start: 0, end: 4)): $couleur = "td_b"; break;
                case in_array(needle: $i, haystack: range(start: 5, end: 9)): $couleur = "td_o"; break;
                case in_array(needle: $i, haystack: range(start: 10, end: 14)): $couleur = "td_v"; break;
                case in_array(needle: $i, haystack: range(start: 15, end: 19)): $couleur = "td_r"; break;
            }

            $table .= HtmlVue::classement(
                "tr_1",
                $i + 1,
                ($this->statistique_repository->equipe(id_equipe: $stat_classement[$i]['id']))->getLogo(),
                $stat_classement[$i]['club'],
                $journee->getNumero(),
                $stat_classement[$i]['victoire'],
                $stat_classement[$i]['nul'],
                $stat_classement[$i]['defaite'],
                $stat_classement[$i]['but_marque'],
                $stat_classement[$i]['but_encaisse'],
                $stat_classement[$i]['but_difference'],
                $stat_classement[$i]['point'],
                Utilitaire::tableau_portion(donnees: $stat_classement[$i]['performance'], nombre: 5),
                "img_30",
                "td_1",
                $couleur,
            );           
        }        
        $table .= '</table>';
        return new JsonResponse(data:['code'=> 'SUCCES', 'data'=> $table]);         
    }

    private function formulaire(mixed ...$donnees): FormInterface
    {
        $form = ($donnees[0])->create(type: $donnees[1], options: [
            'attr' => ['id' => $donnees[2]],
        ]);
        return $form;
    }

    private function stat_classement(mixed $repository, 
    int $saison, 
    int $championnat, 
    int $numero_journee, 
    int $domicile,
    int $exterieur,
    int $premiere_mt,
    int $seconde_mt,
    mixed $data) : array
    {
        // Les variables
        $liste_rencontre = [];
        $calendrier = null;
        $journee = null;
        $clubs = null;
        $club = null;
        $cpt = 0;
        $score_domicile = 0;
        $score_exterieur = 0;
        $id_equipe_domicile = 0;
        $id_equipe_exterieur = 0;

        // On récupère les clubs
        $clubs = $repository->club($saison, $championnat);

        // On parcours les clubs
        foreach($clubs as $c)
        {
            $club[$cpt]['club'] = $c->getEquipe()->getNom(); 
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

        // On parcours les journées
        for($i = 0; $i < $numero_journee; $i++)
        {
            // On récupère la journée
            $journee = $repository->journeeByNumero($i + 1);
            
            // on récupère le calendrier
            $calendrier = $repository->calendrier($championnat, $journee->getId());

            // On récupère la liste des rencontres
            $liste_rencontre = $repository->rencontres($saison, $calendrier->getId());

            // On parcours les rencontres
            foreach($liste_rencontre as $rencontre)
            {
                // On initialise les valeurs
                $id_equipe_domicile = 0;
                $id_equipe_exterieur = 0; 
                $score_domicile = 0;
                $score_exterieur = 0;

                // On parcours les datas
                foreach($data as $donnees)
                {
                    if($donnees['rencontre'] == $rencontre->getId())
                    {

                        if($donnees['preponderance'] == $domicile)
                        {
                            if($donnees['periode'] == $premiere_mt)
                            {
                                $score_domicile += ($donnees['statistique'])->getScore();
                            }else if($donnees['periode'] == $seconde_mt)
                            {
                                $score_domicile += ($donnees['statistique'])->getScore();
                                $id_equipe_domicile = $donnees['equipe']; // On récupère l'id de l'équipe à domicile
                            }
                        }else if($donnees['preponderance'] == $exterieur)
                        {
                            if($donnees['periode'] == $premiere_mt)
                            {
                                $score_exterieur += ($donnees['statistique'])->getScore();
                            }else if($donnees['periode'] == $seconde_mt)
                            {
                                $score_exterieur += ($donnees['statistique'])->getScore();
                                $id_equipe_exterieur = $donnees['equipe']; // On récupère l'id de l'équipe à l'extérieur
                            }
                        }                        
                    }
                }

                // On sauvegarde les statistiques par équipe
                for($j = 0; $j < count(value: $club); $j++)
                {                    
                    if($club[$j]['id'] == $id_equipe_domicile)
                    {    
                        // SCORE--------------------------                    
                        if($score_domicile > $score_exterieur)
                        {
                            $club[$j]['victoire'] += 1; // VICTOIRE
                            $club[$j]['point'] += 3; // POINT                            
                            $club[$j]['performance'][] = Utilitaire::VICTOIRE; // PERFORMANCE                            
                        }else if($score_domicile < $score_exterieur)
                        {
                            $club[$j]['defaite'] += 1; // DEFAITE
                            $club[$j]['performance'][] = Utilitaire::DEFAITE; // PERFORMANCE
                        }else{
                            $club[$j]['nul'] += 1; // NUL
                            $club[$j]['point'] += 1; // POINT
                            $club[$j]['performance'][] = Utilitaire::NUL; // PERFORMANCE
                        }
                        //-------------------------------

                        // BUT -------------------------
                        $club[$j]['but_marque'] += $score_domicile;
                        $club[$j]['but_encaisse'] += $score_exterieur;
                        $club[$j]['but_difference'] = $club[$j]['but_marque'] - $club[$j]['but_encaisse'];
                        //--------------------------                        
                    }else if($club[$j]['id'] == $id_equipe_exterieur)
                    {    
                        // SCORE--------------------------                    
                        if($score_exterieur > $score_domicile)
                        {
                            $club[$j]['victoire'] += 1; // VICTOIRE
                            $club[$j]['point'] += 3; // POINT                            
                            $club[$j]['performance'][] = Utilitaire::VICTOIRE; // PERFORMANCE                            
                        }else if($score_exterieur < $score_domicile)
                        {
                            $club[$j]['defaite'] += 1; // DEFAITE
                            $club[$j]['performance'][] = Utilitaire::DEFAITE; // PERFORMANCE
                        }else{
                            $club[$j]['nul'] += 1; // NUL
                            $club[$j]['point'] += 1; // POINT
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
        }

        // On effectue le classement des équipes par points, difference de buts et buts marqués
        for($i = 0; $i < (count(value: $club) - 1); $i++) // On parcourt la première équipe
        {
            for($j = $i + 1; $j < count(value: $club); $j++) // On parcourt la seconde équipe
            {
                if($club[$i]['point'] < $club[$j]['point']) // On compare les points
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

                }else if($club[$i]['point'] == $club[$j]['point'])
                {
                    if($club[$i]['but_difference'] < $club[$j]['but_difference']) // On compare la différence de but
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
                        
                    }else if($club[$i]['but_difference'] == $club[$j]['but_difference'])
                    {
                        if($club[$i]['but_marque'] < $club[$j]['but_marque']) // On compare le total de but marqué
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

        return $club;
    }
    
}
