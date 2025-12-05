<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
final class TableaudebordController extends AbstractController
{
    #[Route('', name: 'app_tableaudebord')]
    public function index(): Response
    {
        return $this->render('tableaudebord/index.html.twig', [
        ]);
    }
}
