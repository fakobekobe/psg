<?php 

namespace App\Traitement\Abstrait;

use App\Traitement\Interface\TraitementInterface;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class TraitementAbstrait implements TraitementInterface
{
    public function __construct(
        protected ?EntityManagerInterface $em = null, 
        protected ?FormInterface $form = null, 
        protected ?ServiceEntityRepository $repository = null
        )
    {
    }

    public function actionAjouter(mixed ...$donnees) : JsonResponse
    {
        if($donnees[0])
        {
            return $this->actionAjouterSucces(donnees: $donnees);
        }else{
            return $this->actionAjouterEchec(erreurs: Utilitaire::getErreur(form: $this->form));
        }
    }

    protected function actionAjouterSucces(mixed ...$donnees) : JsonResponse
    {
        $objet = $this->form->getData();
        $this->em->persist(object: $objet);
        $this->em->flush();
        return new JsonResponse(data: [
            'code' => self::SUCCES,
        ]);
    }

    protected function actionAjouterEchec(mixed $erreurs) : JsonResponse
    {
        return new JsonResponse(data: [
            'code' => self::ECHEC,
            'erreurs' => $erreurs,
        ]);
    }

    /**
     * traitementActionModifier est une méthode héritée de TraitementInterface
     * Elle traite l'action de modification d'un objet
     * @param mixed ...$donnees est un tableau qui contient les 2 variables suivantes
     * @param mixed $mixe est un objet ou null
     * @param bool $select est un booléen, il définit le cas d'un select
     * @return JsonResponse
     */
    public function actionModifier(mixed ...$donnees) : JsonResponse
    {        
        if($this->form->isSubmitted()) 
        {                      
            if($donnees[0])
            {
                if($donnees[1])
                {
                    return $this->actionModifierSucces(objet: $donnees[2]?? null);
                }else{                    
                    $erreurs = Utilitaire::getErreur(form: $this->form);
                                    
                    if(!count(value: $erreurs))
                    {
                         return $this->actionModifierSucces(objet: $donnees[2] ?? null);
                    }else{
                        return $this->actionModifierEchec(erreurs: $erreurs);
                    }
                }                

            }  else{
                return $this->actionModifierEchec(erreurs: Utilitaire::getErreur(form: $this->form));
            }    
        }else{
            return $this->actionModifierEchec(erreurs: Utilitaire::getErreur(form: $this->form));
        }
    }

    public function actionModifierSucces(mixed $objet = null): JsonResponse
    {
        $this->em->flush();
        return new JsonResponse(data: ['code' => self::SUCCES]);
    }

    public function actionModifierEchec(array $erreurs): JsonResponse
    {
        return new JsonResponse(data: [
            'code' => self::ECHEC,
            'erreurs' => $erreurs,
        ]);
    }

    public function actionLister(mixed ...$donnees) : JsonResponse
    {
        if(count(value: $donnees[0]) > 0)
        {
            return $this->actionListerSucces($donnees[0], $donnees[1] ?? null);
        }else{
            return $this->actionListerEchec();
        }
    }

    protected function actionListerSucces(mixed ...$donnees) : JsonResponse
    {
        return new JsonResponse(data: [
            'code' => self::SUCCES,
            'html' => $this->chaine_data($donnees[0], $donnees[1])
        ]);
    }

    protected function actionListerEchec() : JsonResponse
    {
        return new JsonResponse(data: ['code' => self::ECHEC]);
    }

    public function actionCheck(mixed ...$donnees) : JsonResponse
    {
        $entity = $this->repository->findOneBy(criteria: ['id' => $donnees[0]]);
        $propriete = $donnees[1] ?? null;

        if($entity !== null)
        {
            return $this->actionCheckSucces($entity, $propriete);
        }else{
            return $this->actionCheckEchec();
        }
    }

    protected function actionCheckSucces(mixed ...$donnees) : JsonResponse
    {
        return new JsonResponse(data: [
            'code' => static::SUCCES,
            'objet' => $this->getObjet($donnees[0], $donnees[1] ?? null) // Méthode abstraite définie dans les classes dérivées
        ]);
    }

    protected function actionCheckEchec() : JsonResponse
    {
        return new JsonResponse(data: ['code' => self::ECHEC]);
    }

    /**
     * AationSupprimer est une méthode héritée de TraitementInterface
     * Elle traite l'action de la suppression d'un objet
     * @param mixed $donnees est l'id de l'objet à supprimer
     * @return JsonResponse
     */
    public function actionSupprimer(mixed ...$donnees) : JsonResponse
    {
        $objet = $this->repository->findOneBy(criteria: ['id' => $donnees[0]]);
        if($objet !== null)
        {
            return $this->actionSupprimerSucces(objet: $objet);
        }else{
            return $this->actionSupprimerEchec();
        }
    }

    protected function actionSupprimerSucces(mixed $objet) : JsonResponse
    {
        $this->em->remove(object: $objet);
        $this->em->flush();
        return new JsonResponse(data: [
            'code' => self::SUCCES,
            'message' => "Votre données a bien été supprimée."
        ]);
    }

    protected function actionSupprimerEchec() : JsonResponse
    {
        return new JsonResponse(data: [
            'code' => self::ECHEC,
            'message' => "Impossible de supprimer cette données."
        ]);
    }

    public function actionImprimer(mixed ...$donnees) : JsonResponse
    {
        if($donnees[0])
        {
            return $this->actionImprimerSucces(donnees: $donnees);
        }else{
            return $this->actionImprimerEchec(donnees: $donnees);
        }
    }

    protected function actionImprimerSucces(mixed ...$donnees) : JsonResponse
    {
        return new JsonResponse(data: ['code' => self::SUCCES]);
    }

    protected function actionImprimerEchec(mixed ...$donnees) : JsonResponse
    {
        return new JsonResponse(data: ['code' => self::ECHEC]);
    }

    protected function chaine_data(mixed ...$donnees): string
    {
        $propriete = "get" . ucfirst(string: $donnees[1]); // $donnees[1] contient le nom de la propriété
        
        $tab = "";
        $i = 0;
        $separateur = ';x;';
        $nb = count(value: $donnees[0]); // $donnees[0] contient la liste des objets

        foreach($donnees[0] as $data)
        {
            $valeur = $data->$propriete();
            if(is_int(value: $valeur))
            {
                $valeur = number_format(
                    num: $valeur, 
                    decimals:0, 
                    decimal_separator:'', 
                    thousands_separator:' '
                );

            }else{
                $valeur = nl2br(string: strtoupper(string: htmlspecialchars(string: $valeur)));
            }

            $i++;
            $v = ($i != $nb) ? '!x!':'';
            $tab .=  $i . $separateur . 
            $valeur . $separateur . 
            $this->lien_a(id: $data->getId(), nom: $valeur) . $v;
        }

        return $tab;
    }

    protected function lien_a(mixed ...$donnees): string
    {
        return <<<HTML
    <div class="d-sm-inline-flex">
        <a href="#" class="text-white mr-1 text-success editBtn h1" title="Modifier" data-id="{$donnees[0]}" data-toggle="modal" data-target="#ajouterBackdrop"><i class="typcn typcn-edit"></i></a>
        <a href="#" class="text-white text-danger deleteBtn h1" title="Supprimer" data-id="{$donnees[0]}" data-nom="{$donnees[1]}"><i class="typcn typcn-trash"></i></a>
    </div>
HTML;
    }

    /**
     * getObjet définie les propriétés de l'objet dans l'Action Check qui sont pris en compte dans le js
     * @param mixed $donnees Est un tableau de données à manipuler
     * @return array Le tableau des propriétés de l'objet
     */
    abstract protected function getObjet(mixed ...$donnees) : array;
}