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
use App\Traitement\Interface\ControlleurInterface;

#[Route(path:'/match')]
final class MatchDisputeController extends AbstractController
{
    private const PREFIX_NAME = 'app_match';
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
        );

        if($contenu['reponse'] instanceof Response)
        {
            return $contenu['reponse'];
        }else{
            return $this->render(view: 'match/index.html.twig', parameters: [
            'form' => $contenu['form']->createView(),
        ]);
        }        
    }

    #[Route(path:'/liste/{id}', name: self::PREFIX_NAME . "_liste", methods:["GET"], requirements: ['id' => '[0-9]+'])]
    public function liste(int $id): Response
    {        
        /**
         * Cette fonction liste du controller permet la gestion du retour de la liste des objets
         */
        return $this->controlleur->lister($this->repository, $id);
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
}
