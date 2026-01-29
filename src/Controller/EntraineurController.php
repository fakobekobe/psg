<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use App\Repository\EntraineurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\EntraineurType;
use App\Traitement\Interface\ControlleurInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path:'/entraineur')]
final class EntraineurController extends AbstractController
{
    private const PREFIX_NAME = 'app_entraineur';
    private const TYPEFORM = EntraineurType::class;
    private const PAGE = '_entraineur';
    private EntraineurRepository $repository;
    private ControlleurInterface $controlleur;

    public function __construct(
        private FormFactoryInterface $form, 
        private EntityManagerInterface $em,
        private ManagerRegistry $registry,
        )
    {
        $this->repository = new EntraineurRepository(registry: $this->registry);
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
        $data = null;
        if($request->isMethod(method: 'POST'))
        {            
            $file = $request->files->all(); // Le tableau des fichiers
            $data['image'] = $file['image'];
        } 

        $contenu = $this->controlleur->ajouter(
            $this->repository, 
            $this->form,
            self::TYPEFORM,
            'form_type',
            $request,             
            $this->em,
            $data, 
        );

        if($contenu['reponse'] instanceof Response)
        {
            return $contenu['reponse'];
        }else{
            return $this->render(view: 'entraineur/index.html.twig', parameters: [
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
        $data = null;
        if($request->isMethod(method: 'POST'))
        {            
            $file = $request->files->all(); // Le tableau des fichiers
            $data['image'] = $file['image'];
        }

        return $this->controlleur->modifier(
            $this->repository, 
            $id, 
            $this->form,       
            self::TYPEFORM,
            "form_type",
            $request, 
            $this->em,
            $data,  
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

    #[Route(path:'/telecharger/{id}', name: self::PREFIX_NAME . "_telecharger", methods: ["GET"], requirements: ['id' => '[0-9]+'])]
    public function telecharger(int $id): Response
    {
        /**
         * Cette méthode du controller permet le téléchargement de fichier
         * use Symfony\Component\HttpFoundation\BinaryFileResponse;
         * $this->file() est une méthode de BinaryFileResponse
         * $objet->getPathFichier() est le chemin absolu du fichier
         */
        $objet = $this->repository->findOneBy(criteria: ['id' => $id]);
        return $this->file(file: $objet->getPathFichier());
    }
}
