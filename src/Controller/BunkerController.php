<?php

namespace App\Controller;

use App\Form\MatchDisputeType;
use App\Repository\MatchDisputeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

#[Route(path:'/bunker')]
final class BunkerController extends AbstractController
{
    private const PREFIX_NAME = 'app_bunker';

    public function __construct(
        private ManagerRegistry $registry,
        private FormFactoryInterface $form,
        )
    {
        
    }

    #[Route(path: '', name: self::PREFIX_NAME, methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form_analyse = $this->formulaire(
            $this->form,
            MatchDisputeType::class,
            'form_type',
        );
        return $this->render(view: 'bunker/index.html.twig', parameters:[
            'form_analyse' => $form_analyse->createView(),
        ]);             
    }

    private function formulaire(mixed ...$donnees): FormInterface
    {
        $form = ($donnees[0])->create(type: $donnees[1], options: [
            'attr' => ['id' => $donnees[2]],
        ]);
        return $form;
    }

    
}
