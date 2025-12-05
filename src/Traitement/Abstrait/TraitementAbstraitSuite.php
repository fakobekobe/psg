<?php
namespace App\Traitement\Abstrait;

use App\Traitement\Interface\TraitementInterfaceSuite;
use App\Traitement\Utilitaire\Utilitaire;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class TraitementAbstraitSuite implements TraitementInterfaceSuite
{
    // Traitement de l'action Select ------------------

    /**
     * traitementActionSelect est une méthode qui
     * traite l'action de la sélection d'un objet
     * @param $donnees contien : $donnees[0] = le Repository,  
     * $donnees[1] = le labelle de l'option à afficher par défaut
     * $donnees[2] = l'id de l'objet à selectionner
     * @return JsonResponse
     */
    public function actionSelect(mixed ...$donnees) : JsonResponse
    {
        $objet = ($donnees[0])->findOptionsById(id: $donnees[2]);
        if(!empty($objet))
        {
            return $this->actionSelectSucces(objet: $objet, label: $donnees[1]);
        }else{
            return $this->actionSelectEchec(label: $donnees[1]);
        }
    }

    protected function actionSelectSucces(mixed $objet, string $label) : JsonResponse
    {
        return new JsonResponse(data: [
            'code' => 'SUCCES',
            'html' => Utilitaire::getOptionsSelect(objet: $objet, label: $label)
        ]);
    }

    protected function actionSelectEchec(string $label) : JsonResponse
    {
        return new JsonResponse(data: [
            'code' => 'ECHEC',
            'erreur' => "<option value=\"\">--- ". $label ." ---</option>"
        ]);
    }
}