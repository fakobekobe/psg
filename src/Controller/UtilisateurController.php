<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\RegistrationType;
use App\Traitement\Interface\ControlleurInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path:'/admin/utilisateur')]
final class UtilisateurController extends AbstractController
{
    private const PREFIX_NAME = 'app_utilisateur';
    private const PAGE = '_utilisateur';
    private const TYPEFORM = RegistrationType::class;
    private UtilisateurRepository $repository;
    private ControlleurInterface $controlleur;

    public function __construct(
        private FormFactoryInterface $form, 
        private EntityManagerInterface $em,
        private ManagerRegistry $registry,
        private UserPasswordHasherInterface $userPasswordHasher
        )
    {
        $this->repository = new UtilisateurRepository(registry: $this->registry);
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
            $this->userPasswordHasher,
        );

        if($contenu['reponse'] instanceof Response)
        {
            return $contenu['reponse'];
        }else{
            return $this->render(view: 'utilisateur/index.html.twig', parameters: [
            'form' => $contenu['form']->createView(),
        ]);
        }        
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

    #[Route(path: '/check/{id}', name: self::PREFIX_NAME . '_check', methods: ["POST"] , requirements: ['id' => '[0-9]+'])]
    #[IsGranted(attribute: "modifier" . self::PAGE)]
    public function check(int $id) : Response
    {
        /**
         * Cette méthode liste du controller permet la gestion du chargement des données de la modification du formulaire
         */
        return $this->controlleur->check( $this->repository, $id);
    }

    #[Route(path:'/modifier/{id}', name: self::PREFIX_NAME . "_modifier", methods: ["POST"], requirements: ['id' => '[0-9]+'])]
    #[IsGranted(attribute: "modifier" . self::PAGE)]
    public function modifier(Request $request, int $id) : Response
    {
        /**
         * Cette méthode modifier du controller permet la modification du formulaire
         */
        return $this->controlleur->modifier(
            $this->repository, 
            $id, 
            $this->form,       
            self::TYPEFORM,
            "form_type",
            $request, 
            $this->em,  
            $this->userPasswordHasher,
        );       
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
}
