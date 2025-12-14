<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use App\Repository\StatistiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Traitement\Interface\ControlleurInterface;

#[Route(path:'/statistique')]
final class StatistiqueController extends AbstractController
{
    private const PREFIX_NAME = 'app_statistique';
    private StatistiqueRepository $repository;
    private ControlleurInterface $controlleur;

    public function __construct(
        private FormFactoryInterface $form, 
        private EntityManagerInterface $em,
        private ManagerRegistry $registry,
        )
    {
        $this->repository = new StatistiqueRepository(registry: $this->registry);
        $this->repository->initialiserControlleur();
        $this->controlleur = $this->repository->getControlleur();
    }

    #[Route(path: '', name: self::PREFIX_NAME, methods: ['GET', 'POST'])]
    public function ajouter(Request $request): Response
    {
        /**
         * Cette méthode ajout du controller permet l'ajout des statistiques
         */
        $contenu = $this->controlleur->ajouter($request, $this->repository, $this->em);
        return $contenu['reponse'];
               
    }

    #[Route(path:'/liste/{id_rencontre}', name: self::PREFIX_NAME . "_liste", methods:["GET"], requirements: ['id_rencontre' => '[0-9]+'])]
    public function liste(int $id_rencontre): Response
    {        
        /**
         * Cette fonction liste du controller permet la gestion du retour de la liste des objets selon les variables
         */
        return $this->controlleur->lister($this->repository, $id_rencontre, $this->em);
    }

    #[Route(path:'/check/{id_rencontre}/{id_periode}', name: self::PREFIX_NAME . "_check", methods: ["POST"], requirements: ['id_rencontre' => '[0-9]+', 'id_periode' => '[0-9]+'])]
    public function check(int $id_rencontre, int $id_periode): Response
    {
        /**
         * Cette méthode du controller permet le chargement des données de l'objet
         */
        return $this->controlleur->check(
            $this->repository, 
            $id_rencontre,
            $id_periode,
        );
    }

    #[Route(path:'/modifier/{id_rencontre}/{id_periode}', name: self::PREFIX_NAME . "_modifier", methods: ["POST"], requirements: ['id_rencontre' => '[0-9]+', 'id_periode' => '[0-9]+'])]
    public function modifier(Request $request, int $id_rencontre, int $id_periode): Response
    {
        /**
         * Cette méthode du controller permet la modification d'un objet
         */
        return $this->controlleur->modifier(
            $request,
            $this->repository, 
            $this->em,
            $id_rencontre,
            $id_periode,
        );
    }

    #[Route(path:'/supprimer/{id_rencontre}/{id_periode}', name: self::PREFIX_NAME . "_supprimer", methods: ["POST"], requirements: ['id_rencontre' => '[0-9]+', 'id_periode' => '[0-9]+'])]
    public function supprimer(int $id_rencontre, int $id_periode): Response
    {
        /**
         * Cette méthode du controller permet la suppression d'un objet
         */
        return $this->controlleur->supprimer(
            $this->repository, 
            $this->em,  
            $id_rencontre,
            $id_periode,
        );
    }
}
