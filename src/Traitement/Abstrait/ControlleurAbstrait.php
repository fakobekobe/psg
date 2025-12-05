<?php
namespace App\Traitement\Abstrait;

use App\Traitement\Interface\ControlleurInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class ControlleurAbstrait implements ControlleurInterface
{
    /**
     * Description de la méthode ajouter
     * @param mixed[] $donnees 
     * $donnees[0] = Repository
     * $donnees[1] = FormFactoryInterface
     * $donnees[2] = TypeFormulaire
     * $donnees[3] = "form_type"
     * $donnees[4] = request
     * $donnees[5] = em
     * @return array
     */
    public function ajouter(mixed ...$donnees): array
    {
        $objet = ($donnees[0])->new();
        $form = ($donnees[1])->create(type: $donnees[2], data: $objet, options: [
            'attr' => ['id' => $donnees[3]],
        ]);

        $form->handleRequest(request: $donnees[4]);
        $retour['form'] = $form;

        if ($form->isSubmitted()) {
            $estValide = $form->isValid();

            // Initialisation du traitement
            ($donnees[0])->initialiserTraitement(em: $donnees[5], form: $form, repository: $donnees[0]);           

            // Appel et récupération des données de la méthode du traitement
            $retour['reponse'] = (($donnees[0])->getTraitement())->actionAjouter($estValide, $objet);
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }

    /**
     * Description de lister
     * @param mixed[] $donnees 
     * $donnees[0] = Repository
     * $donnees[1] = [] de critaire
     * $donnees[2] = propriété
     * $donnees[3] = em
     * @return JsonResponse
     */
    public function lister(mixed ...$donnees): JsonResponse
    {
        $critaire = $donnees[1] ?? [];
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0], em: $donnees[3] ?? null); 

        // On récupère la liste des objets
        $liste = ($donnees[0])->findBy(criteria: $critaire, orderBy: ['id' => "DESC"]);

        $propriete = $donnees[2] ?? null; // $donnees[2] est la propriété de l'objet

        return (($donnees[0])->getTraitement())->actionLister($liste, $propriete);
    }

    /**
     * Description de check
     * @param mixed[] $donnees 
     * $donnees[0] = Repository
     * $donnees[1] = id
     * $donnees[2] = propriété
     * @return JsonResponse
     */
    public function check(mixed ...$donnees): JsonResponse
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0]);

        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($donnees[0])->getTraitement()->actionCheck($donnees[1], $donnees[2] ?? null);
    }

    /**
     * Description de modifier
     * @param mixed[] $donnees
     * $donnees[0] = Repository
     * $donnees[1] = id
     * $donnees[2] = FormFactoryInterface
     * $donnees[3] = TypeForm
     * $donnees[4] = "form_type"
     * $donnees[5] = request
     * $donnees[6] = em
     * @return JsonResponse
     */
    public function modifier(mixed ...$donnees): JsonResponse
    {
        $objet = ($donnees[0])->findOneBy(criteria: ['id' => $donnees[1]]);
        $form = ($donnees[2])->create(type: $donnees[3], data: $objet, options: [
            'attr' => ['id' => $donnees[4]]
        ]);

        $form->handleRequest(request: $donnees[5]);
        $is_select = $form->isValid();
        
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(form: $form, em: $donnees[6]);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($donnees[0])->getTraitement()->actionModifier($objet, $is_select);
    }

    /**
     * Description de supprimer
     * @param mixed[] $donnees 
     * $donnees[0] = Repository
     * $donnees[1] = em
     * $donnees[2] = id
     * @return JsonResponse
     */
    public function supprimer(mixed ...$donnees): JsonResponse
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(em: $donnees[1], repository: $donnees[0]);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($donnees[0])->getTraitement()->actionSupprimer($donnees[2]);
    }

    public function select(mixed ...$donnees): JsonResponse
    {
        return new JsonResponse(data: []);
    }

    public function imprimer(mixed ...$donnees): JsonResponse
    {
        return new JsonResponse(data: []);
    }
}