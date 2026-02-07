<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use App\Repository\MatchDisputeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\MatchDisputeType;
use App\Form\MatchType;
use App\Traitement\Interface\ControlleurInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path:'/match')]
final class MatchDisputeController extends AbstractController
{
    private const PREFIX_NAME = 'app_match';
    private const PAGE = '_matchdispute';
    private const TYPEFORM = MatchDisputeType::class;
    private MatchDisputeRepository $repository;
    private ControlleurInterface $controlleur;

    public function __construct(
        private FormFactoryInterface $form, 
        private EntityManagerInterface $em,
        private ManagerRegistry $registry,
        )
    {
        $this->repository = new MatchDisputeRepository(registry: $this->registry);
        $this->repository->initialiserControlleur();
        $this->controlleur = $this->repository->getControlleur();
    }

    #[Route(path: '', name: self::PREFIX_NAME, methods: ['GET', 'POST'])]
    #[IsGranted(attribute: "ajouter" . self::PAGE)]
    public function ajouter(Request $request): Response
    {
        /**
         * Cette fonction ajouter du controller permet l'affichage et l'ajout des données
         */

        $contenu = $this->controlleur->ajouter(
            $this->repository, 
            $this->form,
            self::TYPEFORM,
            'form_type',
            $request,             
            $this->em, 
            MatchType::class,
        );

        if($contenu['reponse'] instanceof Response)
        {
            return $contenu['reponse'];
        }else{
            return $this->render(view: 'match/index.html.twig', parameters: [
            'form' => $contenu['form']->createView(),
            'periode' => $contenu['periode']->createView(),
        ]);
        }        
    }

    #[Route(path:'/liste/{id_calendrier}/{id_saison}', name: self::PREFIX_NAME . "_liste", methods:["GET"], requirements: ['id_calendrier' => '[0-9]+', 'id_saison' => '[0-9]+'])]
    #[IsGranted(attribute: "ajouter" . self::PAGE)]
    public function liste(int $id_calendrier, int $id_saison): Response
    {        
        /**
         * Cette fonction liste du controller permet la gestion du retour de la liste des objets
         */
        return $this->controlleur->lister($this->repository, $id_calendrier, $id_saison, $this->em);
    }

    #[Route(path: '/rencontre_equipe', name: self::PREFIX_NAME . '_rencontre_equipe', methods: ["POST"])]
    public function rencontre_equipe(Request $request) : Response
    {
        /**
         * Cette méthode liste du controller permet la gestion du chargement des données de la modification du formulaire
         */
        return $this->controlleur->rencontre_equipe($request, $this->repository);
    }

    #[Route(path:'/supprimer/{id}', name: self::PREFIX_NAME . "_supprimer", methods: ["POST"], requirements: ['id' => '[0-9]+'])]
    #[IsGranted(attribute: "supprimer" . self::PAGE)]
    public function supprimer(int $id): Response
    {
        /**
         * Cette méthode du controller permet la suppression d'un objet
         */
        return $this->controlleur->supprimer(
            $this->repository, 
            $this->em,  
            $id,
        );
    }

    #[Route(path:'/periode/{id}', name: self::PREFIX_NAME . "_periode", methods: ["GET"], requirements: ['id' => '[0-9]+'])]
    public function periode(int $id): Response
    {
        /**
         * Cette méthode du controller permet le chargement des équipes
         */
        return $this->controlleur->periode(
            $this->repository, 
            $id,
        );
    }

    #[Route(path: '/statistique', name: self::PREFIX_NAME . '_statistique', methods: ["POST"])]
    public function statistique(Request $request) : Response
    {
        /**
         * Cette méthode statistique du controller permet l'ajout des statistiques
         */
        return $this->controlleur->statistique($request, $this->repository, $this->em);
    }

    #[Route(path:'/liste_statistique/{id_rencontre}', name: self::PREFIX_NAME . "_liste_statistique", methods:["GET"], requirements: ['id_rencontre' => '[0-9]+'])]
    public function liste_statistique(int $id_rencontre): Response
    {        
        /**
         * Cette fonction liste_statistique du controller permet la gestion du retour de la liste des objets selon les variables
         */
        return $this->controlleur->liste_statistique($this->repository, $id_rencontre);
    }

    #[Route(path:'/supprimer_statistique/{id_rencontre}/{id_periode}', name: self::PREFIX_NAME . "_supprimer_statistique", methods: ["POST"], requirements: ['id_rencontre' => '[0-9]+', 'id_periode' => '[0-9]+'])]
    #[IsGranted(attribute: "supprimer" . self::PAGE)]
    public function supprimer_statistique(int $id_rencontre, int $id_periode): Response
    {
        /**
         * Cette méthode du controller permet la suppression d'un objet
         */
        return $this->controlleur->supprimer_statistique(
            $this->repository, 
            $this->em,  
            $id_rencontre,
            $id_periode,
        );
    }
}
