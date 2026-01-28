<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\GroupeUtilisateurRepository; 
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Traitement\Interface\ControlleurInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route(path: '/admin/groupe-utilisateur')]
final class GroupeUtilisateurController extends AbstractController
{
    private const PREFIX_NAME = 'app_groupe_utilisateur';
    private GroupeUtilisateurRepository $repository;
    private ControlleurInterface $controlleur;

    public function __construct(
        private EntityManagerInterface $em,
        private ManagerRegistry $registry,
    ){
        $this->repository = new GroupeUtilisateurRepository(registry: $this->registry);
        $this->repository->initialiserControlleur();
        $this->controlleur = $this->repository->getControlleur();
    }

    #[Route(path: '', name: self::PREFIX_NAME , methods:["GET", "POST"])]
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
            return $contenu['reponse'];
        }else{
            return $this->render(view: 'groupe_utilisateur/index.html.twig', parameters: [
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
    public function liste(): Response
    {
        /**
         * Cette fonction liste du controller permet la gestion du retour de la liste des objets
         */
        return $this->controlleur->lister($this->repository);
    }

    #[Route(path: '/check/{id}', name: self::PREFIX_NAME . '_check', methods: ["POST"] ,requirements: ['id' => '[0-9]+'])]
    public function check(int $id, Request $request) : Response
    {
        /**
         * Cette méthode check du controller permet la gestion du chargement des données pour la modification du formulaire
         */
        return $this->controlleur->formulaire(
            $this->repository, 
            $this->em, 
            $id, 
        ); 
    }

    #[Route(path:'/modifier/{id}', name: self::PREFIX_NAME . "_modifier", methods: ["POST"], requirements: ['id' => '[0-9]+'])]
    public function modifier(Request $request, int $id) : Response
    {
        /**
         * Cette méthode modifier du controller permet la modification du formulaire
         */
        return $this->controlleur->modifier( //modifier_groupe_utilisateur
            $this->repository, 
            $request, 
            $this->em,  
            $id,
        );       
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
