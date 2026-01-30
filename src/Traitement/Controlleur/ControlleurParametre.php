<?php
namespace App\Traitement\Controlleur;

use App\Traitement\Abstrait\ControlleurAbstrait;

class ControlleurParametre extends ControlleurAbstrait
{
    public function ajouter(mixed ...$donnees): array
    {
        $objet = ($donnees[0])->new();
        $form = ($donnees[1])->create(type: $donnees[2], data: $objet, options: [
            'attr' => ['id' => $donnees[3]],
        ]);

        $form->handleRequest(request: $donnees[4]);
        $retour['form'] = $form;

        if ($form->isSubmitted()) {
            $objet = (($donnees[4])->request->all())['parametre'];

            // Initialisation du traitement
            ($donnees[0])->initialiserTraitement(em: $donnees[5], form: $form, repository: $donnees[0]);           

            // Appel et récupération des données de la méthode du traitement
            $retour['reponse'] = (($donnees[0])->getTraitement())->actionAjouter(true, $objet);
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }
}