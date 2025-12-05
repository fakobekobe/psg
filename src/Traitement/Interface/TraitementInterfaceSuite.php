<?php

namespace App\Traitement\Interface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

interface TraitementInterfaceSuite
{
    public function actionSelect(mixed ...$donnees) : Response;
}