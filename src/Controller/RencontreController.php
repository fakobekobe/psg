<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use App\Repository\RencontreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\RencontreType;
use App\Traitement\Interface\ControlleurInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path:'/rencontre')]
final class RencontreController extends AbstractController
{
    private const PREFIX_NAME = 'app_rencontre';
    private const PAGE = '_rencontre';
    private const TYPEFORM = RencontreType::class;
    private RencontreRepository $repository;
    private ControlleurInterface $controlleur;

    public function __construct(
        private FormFactoryInterface $form, 
        private EntityManagerInterface $em,
        private ManagerRegistry $registry,
        )
    {
        $this->repository = new RencontreRepository(registry: $this->registry);
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
        // Gestion du cas du champ select ----------------------
        $select = $this->getObjetSelect(request: $request, repo: $this->repository, formType: 'rencontre', champ: 'calendrier');         
        //-----------------------------------------------------------------

        $contenu = $this->controlleur->ajouter(
            $this->repository, 
            $this->form,
            self::TYPEFORM,
            'form_type',
            $request,             
            $this->em, 
            $select,
        );

        if($contenu['reponse'] instanceof Response)
        {
            return $contenu['reponse'];
        }else{
            return $this->render(view: 'rencontre/index.html.twig', parameters: [
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

        // Gestion du cas du champ select ----------------------
        $select = $this->getObjetSelect(request: $request, repo: $this->repository, formType: 'rencontre', champ: 'calendrier');         
        //-----------------------------------------------------------------

        return $this->controlleur->modifier(
            $this->repository, 
            $id, 
            $this->form,       
            self::TYPEFORM,
            "form_type",
            $request, 
            $this->em, 
            $select, 
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

    /**
     * Fonction Interne permettant la création du tableau de gestion du select
     */
    private function getObjetSelect(Request $request, ServiceEntityRepository $repo, string $formType, string $champ): ?array
    {
        $objet = null; // La variable contenu l'objet
        if($request->isMethod(method: 'POST'))
        {
            // On récupère l'id du champ select
            $id_objet = (int)(($request->request->all())[$formType][$champ]);   

            // On récupère l'objet 
            $objet = $repo->findSelect(id: $id_objet);

            // On définit les données à retourner
            if($objet)
            {
                $objet = [
                'libelle' => $champ,
                'objet' => $objet
                ];
            }
            
        } 
        return $objet;
    }
}
