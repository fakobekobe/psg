<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ReinitialiserMotDePasseType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/compte')]
#[IsGranted(attribute: 'IS_AUTHENTICATED')]
final class ReinitialiserMotDePasseController extends AbstractController
{
    private const PREFIX_NAME = 'app_compte';

    public function __construct(
        private EntityManagerInterface $em,
        )
    {
    }

    #[Route(path: '/reinitialiser-mot-de-passe', name: self::PREFIX_NAME . '_reinitialiser_mot_de_passe', methods:['GET', 'POST'])]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(type: ReinitialiserMotDePasseType::class, data: null, options:[
            'attr' => [
                'id' => 'form_type'
            ]
        ]);

        $form->handleRequest(request: $request);

        if($form->isSubmitted() and $form->isValid())
        {
            $plainPassword = $form->get(name: 'plainPassword')->getData();
            $user = $this->getUser();
            // Encode(hash) the plain password, and set it.
            $user->setPassword($passwordHasher->hashPassword(user: $user, plainPassword: $plainPassword));
            $this->em->flush();

            // On déconnecte l'utilisateur
            return $this->redirectToRoute('app_logout');
        }

        return $this->render(view: 'reinitialiser_mot_de_passe/index.html.twig', parameters: [
            'form' => $form->createView(),
        ]);
    }
}