<?php
namespace App\Traitement\Controlleur;

use App\Traitement\Abstrait\ControlleurAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Symfony\Component\HttpFoundation\JsonResponse;

class ControlleurGroupeUtilisateur extends ControlleurAbstrait
{
    public function ajouter(mixed ...$donnees): array
    {        
        $retour = [];

        if (($donnees[1])->isMethod(method: 'POST')) {
            // On récupère les données du formulaire (groupe et gerant)
            $objet = ($donnees[1])->request->all();            

            // Initialisation du traitement
            ($donnees[0])->initialiserTraitement(em: $donnees[2], repository: $donnees[0]);           

            // Appel et récupération des données de la méthode du traitement
            $retour['reponse'] = (($donnees[0])->getTraitement())->actionAjouter(true, $objet);
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }

    public function lister(mixed ...$donnees): JsonResponse
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0]); 

        // On récupère la liste des objets
        $liste = ($donnees[0])->getGroupeUtilisateur();
        
        return (($donnees[0])->getTraitement())->actionLister($liste);
    }

    public function modifier(mixed ...$donnees): JsonResponse
    {
        // On récupère les données du formulaire (groupe et gerant)
        $objet['data'] = ($donnees[1])->request->all();
        $objet['id'] = $donnees[3];

        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(em: $donnees[2], repository: $donnees[0]);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return (($donnees[0])->getTraitement())->actionModifier($objet);
    }
    
    public function formulaire(mixed ...$donnees): JsonResponse {
        // Initialisation du traitement
        ($donnees[0])->initialiserTraitement(em: $donnees[1], repository: $donnees[0]);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return (($donnees[0])->getTraitement())->actionFormulaire($donnees[0], $donnees[2] ?? null);
    }

    public function imprimer(mixed ...$donnees): JsonResponse
    {
        // Initialisation du traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0]);           

        // Appel et récupération des données de la méthode du traitement
        return (($donnees[0])->getTraitement())->actionImprimer($donnees[1]);
    }
}