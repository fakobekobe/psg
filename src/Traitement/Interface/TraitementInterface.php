<?php

namespace App\Traitement\Interface;

use Symfony\Component\HttpFoundation\JsonResponse;

interface TraitementInterface
{
    // Les constantes
    public const SUCCES = "SUCCES";
    public const ECHEC = "ECHEC";
    public const EXCEPTION = "EXCEPTION";

    // Les méthodes
    public function actionAjouter(mixed ...$donnees) : JsonResponse;
    public function actionModifier(mixed ...$donnees) : JsonResponse;
    public function actionLister(mixed ...$donnees) : JsonResponse;
    public function actionCheck(mixed ...$donnees) : JsonResponse;
    public function actionSupprimer(mixed ...$donnees) : JsonResponse;
    public function actionImprimer(mixed ...$donnees) : JsonResponse;
}


