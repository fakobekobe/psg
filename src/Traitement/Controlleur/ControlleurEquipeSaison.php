<?php
namespace App\Traitement\Controlleur;

use App\Traitement\Abstrait\ControlleurAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Symfony\Component\HttpFoundation\JsonResponse;

class ControlleurEquipeSaison extends ControlleurAbstrait
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
            $estValide = false;

            $erreur = Utilitaire::getErreur(form: $form);
            // Gestion d'un seul select
            if (count(value: $erreur) <= 1 && $erreur[$donnees[6]['libelle']] && $donnees[6]['objet']) {
                $estValide = true;
                $objet = $donnees[6]['objet'];
            }

            // Initialisation du traitement
            ($donnees[0])->initialiserTraitement(em: $donnees[5], form: $form, repository: $donnees[0]);           

            // Appel et récupération des données de la méthode du traitement
            $retour['reponse'] = (($donnees[0])->getTraitement())->actionAjouter($estValide, $objet);
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }

    public function modifier(mixed ...$donnees): JsonResponse
    {
        $objet = ($donnees[0])->findOneBy(criteria: ['id' => $donnees[1]]);
        $form = ($donnees[2])->create(type: $donnees[3], data: $objet, options: [
            'attr' => ['id' => $donnees[4]]
        ]);

        $form->handleRequest(request: $donnees[5]);

        $estValide = false;
        if($form->isSubmitted())
        {
            $erreur = Utilitaire::getErreur(form: $form);
            // Gestion d'un seul select
            if (count(value: $erreur) <= 1 && $erreur[$donnees[7]['libelle']] && $donnees[7]['objet']) {
                $objet->setEquipe($donnees[7]['objet']);
                $estValide = true;
            }
            
        }        
        
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(form: $form, em: $donnees[6], repository: $donnees[0]);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($donnees[0])->getTraitement()->actionModifier($estValide, $objet, $donnees[1]);
    }
}