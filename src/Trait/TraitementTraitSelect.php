<?php

namespace App\Trait;

use App\Traitement\Utilitaire\Utilitaire;
use Symfony\Component\HttpFoundation\JsonResponse;

trait TraitementTraitSelect
{
    // Traitement de l'action Select ------------------

    /**
     * traitementActionSelect est une méthode qui
     * traite l'action de la sélection d'un objet
     * @param $label est le labelle de l'option à afficher par défaut
     * @param mixed $mixe est l'id de l'objet à selectionner
     * @return JsonResponse
     */
    public function actionSelect(string $label, mixed $mixe = null) : JsonResponse
    {
        $objet = $this->findOptionsById(id: $mixe);
        if(!empty($objet))
        {
            return $this->actionSelectSucces(objet: $objet, label: $label);
        }else{
            return $this->actionSelectEchec(label: $label);
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