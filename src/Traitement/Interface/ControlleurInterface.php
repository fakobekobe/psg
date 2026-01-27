<?php
namespace App\Traitement\Interface;

use Symfony\Component\HttpFoundation\Response;

interface ControlleurInterface
{
    public function ajouter(mixed ...$donnees): array;
    public function lister(mixed ...$donnees): Response;
    public function check(mixed ...$donnees): Response;
    public function modifier(mixed ...$donnees): Response;
    public function supprimer(mixed ...$donnees): Response;
    public function select(mixed ...$donnees): Response;
    public function imprimer(mixed ...$donnees): Response;
    public function formulaire(mixed ...$donnees): Response;
}