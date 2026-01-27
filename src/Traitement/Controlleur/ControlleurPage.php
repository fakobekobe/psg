<?php
namespace App\Traitement\Controlleur;

use App\Traitement\Abstrait\ControlleurAbstrait;

class ControlleurPage extends ControlleurAbstrait
{
    public function ajouter(mixed ...$donnees): array
    {
        $retour = [];

        if (($donnees[1])->isMethod(method: 'POST')) {

            // Initialisation du traitement
            ($donnees[0])->initialiserTraitement(em: $donnees[2], repository: $donnees[0]);           

            // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
            // Ensuite on appelle la méthode appropriée pour traiter l'action
            $retour['reponse'] = (($donnees[0])->getTraitement())->actionAjouter(true, null);
            return $retour;
        } else {
            $retour['reponse'] = null;
            return $retour;
        }
    }
}