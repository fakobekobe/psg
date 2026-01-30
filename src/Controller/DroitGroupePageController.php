<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DroitGroupePageRepository; 
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Traitement\Interface\ControlleurInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[Route(path: '/admin/droit-groupe-page')]
final class DroitGroupePageController extends AbstractController
{
    private const PREFIX_NAME = 'app_droit_groupe_page';
    private const PAGE = '_droitgroupepage';
    private DroitGroupePageRepository $repository;
    private ControlleurInterface $controlleur;

    public function __construct(
        private EntityManagerInterface $em,
        private ManagerRegistry $registry,
        private TokenStorageInterface $tokenStorage,
    ){
        $this->repository = new DroitGroupePageRepository(registry: $this->registry);
        $this->repository->initialiserControlleur();
        $this->controlleur = $this->repository->getControlleur();
    }

    #[Route(path: '', name: self::PREFIX_NAME , methods:["GET", "POST"])]
    #[IsGranted(attribute: "ajouter" . self::PAGE)]
    public function ajouter(Request $request): Response
    {              
        /**
         * Cette fonction ajouter du controller permet l'affichage et l'ajout des données
         */
        $contenu = $this->controlleur->ajouter(
            $this->repository, 
            $request,             
            $this->em, 
        );        

        if($contenu['reponse'] instanceof Response)
        {
            // On reconnecte l'utilisateur déconnecté
            $utilisateur = $this->getUser();
            $this->tokenStorage->setToken(token: null);
            $this->tokenStorage->setToken(token: new UsernamePasswordToken(user: $utilisateur, firewallName: 'main', roles: $utilisateur->getRoles()));            
            return $contenu['reponse'];
        }else{
            return $this->render(view: 'droit_groupe_page/index.html.twig', parameters: [
            ]);
        } 
    }

    #[Route(path: '/formulaire', name: self::PREFIX_NAME . "_formulaire", methods:["POST"])]
    public function formulaire(Request $request): Response
    {   
        /**
         * Cette fonction formulaire du controller permet l'affichage des formulaires
         */
        return $this->controlleur->formulaire(
            $this->repository, 
            $this->em,  
        );       
    }

    #[Route(path:'/liste', name: self::PREFIX_NAME . "_liste", methods:["GET"])]
    #[IsGranted(attribute: "ajouter" . self::PAGE)]
    public function liste(): Response
    {
        /**
         * Cette fonction liste du controller permet la gestion du retour de la liste des objets
         */
        return $this->controlleur->lister($this->repository);
    }

    #[Route(path: '/check/{id}', name: self::PREFIX_NAME . '_check', methods: ["POST"] ,requirements: ['id' => '[0-9]+'])]
    #[IsGranted(attribute: "modifier" . self::PAGE)]
    public function check(int $id, Request $request) : Response
    {
        /**
         * Cette méthode check du controller permet la gestion du chargement des données pour la modification du formulaire
         */
        return $this->controlleur->formulaire(
            $this->repository, 
            $this->em, 
            $this->repository->findBy(criteria: ['id' => $id]), 
        ); 
    }

    #[Route(path:'/modifier/{id}', name: self::PREFIX_NAME . "_modifier", methods: ["POST"], requirements: ['id' => '[0-9]+'])]
    #[IsGranted(attribute: "modifier" . self::PAGE)]
    public function modifier(Request $request, int $id) : Response
    {
        /**
         * Cette méthode modifier du controller permet la modification du formulaire
         */
        $contenu = $this->controlleur->modifier(
            $this->repository, 
            $request, 
            $this->em,  
            $id,
            $this->getUser(),
        ); 
        
        // On reconnecte l'utilisateur déconnecté
            $utilisateur = $this->getUser();
            $this->tokenStorage->setToken(token: null);
            $this->tokenStorage->setToken(token: new UsernamePasswordToken(user: $utilisateur, firewallName: 'main', roles: $utilisateur->getRoles()));            
            return $contenu;
    }

    #[Route(path:'/supprimer/{id}', name: self::PREFIX_NAME . "_supprimer", methods: ["POST"], requirements: ['id' => '[0-9]+'])]
    #[IsGranted(attribute: "supprimer" . self::PAGE)]
    public function supprimer(int $id): Response
    {
        /**
         * Cette méthode du controller permet la suppression d'un objet
         */
        $retour = $this->controlleur->supprimer(
            $this->repository, 
            $this->em,  
            $id,
        );

        // On reconnecte l'utilisateur déconnecté
        $utilisateur = $this->getUser();
        $this->tokenStorage->setToken(token: null);
        $this->tokenStorage->setToken(token: new UsernamePasswordToken(user: $utilisateur, firewallName: 'main', roles: $utilisateur->getRoles()));
        return $retour;
    }

    #[Route(path:'/afficher', name: self::PREFIX_NAME . "_afficher", methods:["POST"])]
    public function afficher(Request $request): Response
    {
        $id = (int) $request->request->get(key: 'id'); // Id de l'utilisateur
        /**
         * Cette fonction afficher du controller permet l'affichage des détails de l'objet
         */
        return $this->controlleur->imprimer($this->repository, $id); // afficher
    }
}
