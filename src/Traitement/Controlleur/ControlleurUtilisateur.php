<?php
namespace App\Traitement\Controlleur;

use App\Traitement\Abstrait\ControlleurAbstrait;
use Symfony\Component\HttpFoundation\JsonResponse;

class ControlleurUtilisateur extends ControlleurAbstrait
{
    public function ajouter(mixed ...$donnees): array {
        $retour = [];
        $objet = ($donnees[0])->new();
        $form = ($donnees[1])->create(type: $donnees[2], data: null, options: [
            'attr' => ['id' => $donnees[3]]
        ]);

        $form->handleRequest(request: $donnees[4]);
        $retour['form'] = $form;

        if ($form->isSubmitted()) {
            // La variable de vérification de la validation du formulaire                    
            $plainpassword = (($donnees[4])->request->all())['registration']['plainPassword'];
            $objet->setPassword($plainpassword['first']);
            $estValide = $form->isValid();

            // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
            ($donnees[0])->initialiserTraitement(em: $donnees[5], form: $form, repository: $donnees[0], userPasswordHasher: $donnees[6]);

            // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
            // Ensuite on appelle la méthode appropriée pour traiter l'action
            $retour['reponse'] = (($donnees[0])->getTraitement())->actionAjouter($estValide, $objet);
            return $retour;
        } else {
            $retour['reponse'] = null;
            return $retour;
        }
    }

    public function modifier(mixed ...$donnees): JsonResponse
    {
        $objet = ($donnees[0])->findOneBy(criteria: ['id' => $donnees[1]]);
        $form = ($donnees[2])->create(type: $donnees[3], data: null, options: [
            'attr' => ['id' => $donnees[4]]
        ]);

        $form->handleRequest(request: $donnees[5]);
        $plainpassword = (($donnees[5])->request->all())['registration']['plainPassword'];
        $objet->setPassword($plainpassword['first']);
        $estValide = $form->isValid();
        //return new JsonResponse(data: ['code' => 'SUCCES', 'data' => $estValide]);
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(form: $form, em: $donnees[6], userPasswordHasher: $donnees[7]);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($donnees[0])->getTraitement()->actionModifier($objet, $estValide, $objet);
    }
}