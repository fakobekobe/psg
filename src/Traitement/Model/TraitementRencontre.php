<?php

namespace App\Traitement\Model;

use App\Traitement\Abstrait\TraitementAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TraitementRencontre extends TraitementAbstrait
{
    public function __construct(
        protected ?EntityManagerInterface $em = null,
        protected ?FormInterface $form = null,
        protected ?ServiceEntityRepository $repository = null
        
        )
    {
        parent::__construct(em: $this->em, form: $this->form, repository: $this->repository);
    }

    protected function getObjet(mixed ...$donnees): array
    {
        $objet['id'] = ($donnees[0])->getId();
        $objet['championnat'] = ($donnees[0])->getCalendrier()->getChampionnat()->getId();
        $liste = $this->repository->getListeJournees(id_championnat: $objet['championnat']);
        $objet['calendrier'] = $liste ? Utilitaire::getOptionsSelect(objet:$liste, label: 'Calendrier', index: ($donnees[0])->getCalendrier()->getId()) : "<option value=\"\">--- Calendrier ---</option>";
        $objet['temperature'] = ($donnees[0])->getTemperature();
        $objet['dateHeureRencontre'] = ($donnees[0])->getDateHeureRencontreHTML();
        return $objet;
    }

    protected function chaine_data(mixed ...$donnees): string
    {        
        $tab = "";
        $i = 0;
        $separateur = ';x;';
        $nb = count(value: $donnees[0]); // $donnees[0] contient la liste des objets

        foreach($donnees[0] as $data)
        {
            $championnat = ucfirst(string: htmlspecialchars(string: $data->getCalendrier()->getChampionnat()->getNom()));
            $calendrier = $data->getCalendrier()->getJournee()->getDescription();
            $temperature = $data->getTemperatureAfficher();
            $date = $data->getDate();
            $heure = $data->getHeure();

            $nom = $championnat . ' => ' . $calendrier . ' => ' . $temperature . ' => ' . $date . ' à ' . $heure;
            $i++;
            $v = ($i != $nb) ? '!x!':'';
            $tab .=  $i . $separateur . 
            $championnat . $separateur . 
            $calendrier . $separateur . 
            $temperature . $separateur . 
            $date . $separateur . 
            $heure . $separateur . 
            $this->lien_a($data->getId(), $nom) . $v;
        }

        return $tab;
    }

    protected function actionAjouterSucces(mixed ...$donnees) : JsonResponse
    {
        $data = $this->form->getData();
        $data->setCalendrier($donnees['donnees'][1]);
        $this->em->persist(object: $data);
        $this->em->flush();
        return new JsonResponse(data: [
            'code' => self::SUCCES,
        ]);
    }

    public function actionModifier(mixed ...$donnees) : JsonResponse
    {        
        if($donnees[0]) 
        {                      
            return $this->actionModifierSucces(objet: [$donnees[1]]);                 

        }else{
            return $this->actionModifierEchec(erreurs: Utilitaire::getErreur(form: $this->form));
        }
    }

}