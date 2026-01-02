<?php

namespace App\Controller;

use App\Form\MatchDisputeType;
use App\Repository\StatistiqueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route(path:'/bunker')]
final class BunkerController extends AbstractController
{
    private const PREFIX_NAME = 'app_bunker';
    private StatistiqueRepository $statistique_repository;

    public function __construct(
        private ManagerRegistry $registry,
        private FormFactoryInterface $form,
        )
    {
        $this->statistique_repository = new StatistiqueRepository(registry: $registry);
    }

    #[Route(path: '', name: self::PREFIX_NAME, methods: ['GET'])]
    public function index(Request $request): Response
    {
        // $data = $this->statistique_repository->findStatistiqueBySaisonByChampionnatbyCalendrier(
        //     id_saison: 1,
        //     id_championnat: 1,
        //     id_calendrier: 5
        // );
        // dd($data);

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
    public function classement(Request $request): Response
    {
        $reponse = $request->request->all()['match_dispute'];  
        $saison = $reponse['saison']; 
        $championnat = $reponse['championnat']; 
        $calendrier = $reponse['calendrier']; 

        $data = $this->statistique_repository->findStatistiqueBySaisonByChampionnatbyCalendrier(
            id_saison: $saison,
            id_championnat: $championnat,
            id_calendrier: $calendrier
        );
        return new JsonResponse(data:['code'=> 'SUCCES', 'data'=> $data]);         
    }

    private function formulaire(mixed ...$donnees): FormInterface
    {
        $form = ($donnees[0])->create(type: $donnees[1], options: [
            'attr' => ['id' => $donnees[2]],
        ]);
        return $form;
    }

    
}
