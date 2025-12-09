<?php

namespace App\Traitement\Controlleur;

use App\Traitement\Abstrait\ControlleurAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Symfony\Component\HttpFoundation\JsonResponse;

class ControlleurTransfert extends ControlleurAbstrait
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

            $id_equipe = ((($donnees[4])->request->all())['transfert']['equipe']) ?? null;
            $id_saison = ((($donnees[4])->request->all())['transfert']['saison']) ?? null;
            $id_joueur = ((($donnees[4])->request->all())['transfert']['joueur']) ?? null;
            if ($id_equipe and $id_saison and $id_joueur) {
                $objet = ($donnees[0])->getEquipeSaison($id_equipe, $id_saison);
                $estValide = true;
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

        $estValide = false;
        $id_equipe = ((($donnees[5])->request->all())['transfert']['equipe']) ?? null;
        $id_saison = ((($donnees[5])->request->all())['transfert']['saison']) ?? null;
        $id_joueur = ((($donnees[5])->request->all())['transfert']['joueur']) ?? null;

        if ($id_equipe and $id_saison and $id_joueur) {
            $club = ($donnees[0])->getEquipeSaison($id_equipe, $id_saison);
            if($club)
            {
                $objet->setEquipeSaison($club);
                $estValide = true;
            }            
        }

        $form->handleRequest(request: $donnees[5]);        

        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(form: $form, em: $donnees[6], repository: $donnees[0]);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($donnees[0])->getTraitement()->actionModifier($estValide, $objet, $donnees[1]);
    }
}
