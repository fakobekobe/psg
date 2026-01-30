<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use App\Repository\ParametreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\ParametreType;
use App\Traitement\Interface\ControlleurInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path:'/parametre')]
final class ParametreController extends AbstractController
{
    private const PREFIX_NAME = 'app_parametre';
    private const TYPEFORM = parametreType::class;
    private const PAGE = '_parametre';
    private ParametreRepository $repository;
    private ControlleurInterface $controlleur;

    public function __construct(
        private FormFactoryInterface $form, 
        private EntityManagerInterface $em,
        private ManagerRegistry $registry,
        )
    {
        $this->repository = new ParametreRepository(registry: $this->registry);
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
        );

        if($contenu['reponse'] instanceof Response)
        {
            return $contenu['reponse'];
        }else{
            return $this->render(view: 'parametre/index.html.twig', parameters: [
            'form' => $contenu['form']->createView(),
        ]);
        }        
    }


    #[Route(path: '/check', name: self::PREFIX_NAME . '_check', methods: ["GET","POST"])]
    #[IsGranted(attribute: "modifier" . self::PAGE)]
    public function check() : Response
    {
        /**
         * Cette méthode check permet le chargement des données pour l'affichage du formulaire
         */
        $data = [];
        $objet = $this->repository->new();

        if($objet->getId())
        {
            $data['domicile'] = $objet->getDomicile();
            $data['exterieur'] = $objet->getExterieur();
            $data['premiereMT'] = $objet->getPremiereMT();
            $data['secondeMT'] = $objet->getSecondeMT();            
        }

        return new JsonResponse(data: ['code' => 'SUCCES', 'data' => $data]);
    }
}
