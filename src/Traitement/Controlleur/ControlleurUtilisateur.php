<?php
namespace App\Traitement\Controlleur;

use App\Traitement\Abstrait\ControlleurAbstrait;

class ControlleurUtilisateur extends ControlleurAbstrait
{
    public function ajouter(mixed ...$donnees): array {
        $retour = [];
        $objet = ($donnees[0])->new();
        $form = ($donnees[1])->create(type: $donnees[2], data: $objet, options: [
            'attr' => ['id' => $donnees[3]]
        ]);

        $form->handleRequest(request: $donnees[4]);
        $retour['form'] = $form;

        if ($form->isSubmitted()) {
            // La variable de vérification de la validation du formulaire
            $estValide = $form->isValid();
            $objet = null;

            // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
            ($donnees[0])->initTraitement(em: $donnees[5], form: $form, repository: $donnees[0], userPasswordHasher: $donnees[6]);

            // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
            // Ensuite on appelle la méthode appropriée pour traiter l'action
            $retour['reponse'] = (($donnees[0])->getTraitement())->traitementActionAjouter(mixe: $estValide, objet: $objet);
            return $retour;
        } else {
            $retour['reponse'] = null;
            return $retour;
        }
    }
}