<?php

namespace App\Traitement\Model;

use App\Traitement\Abstrait\TraitementAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TraitementEquipeSaison extends TraitementAbstrait
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
        $objet['saison'] = ($donnees[0])->getSaison()->getId();
        $objet['championnat'] = ($donnees[0])->getEquipe()->getChampionnat()->getId();
        $objet['entraineur'] = ($donnees[0])->getEntraineur()->getId();
        $liste = $this->repository->getListeEquipes(id_championnat: $objet['championnat']);
        $objet['equipe'] = $liste ? Utilitaire::getOptionsSelect(objet:$liste, label: 'Equipe', index: ($donnees[0])->getEquipe()->getId()) : "<option value=\"\">--- Equipe ---</option>";
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
            $saison = ucfirst(string: htmlspecialchars(string: $data->getSaison()->getLibelle()));
            $championnat = ucfirst(string: htmlspecialchars(string: $data->getEquipe()->getChampionnat()->getNom()));
            $equipe = ucfirst(string: htmlspecialchars(string: $data->getEquipe()->getNom()));
            $entraineur = ucfirst(string: htmlspecialchars(string: $data->getEntraineur()->getNom()));

            $nom = $saison . ' => ' . $championnat . ' => ' . $equipe . ' => ' . $entraineur;
            $i++;
            $v = ($i != $nb) ? '!x!':'';
            $tab .=  $i . $separateur . 
            $saison . $separateur . 
            $championnat . $separateur . 
            $equipe . $separateur . 
            $entraineur . $separateur . 
            $this->lien_a(id: $data->getId(), nom: $nom) . $v;
        }

        return $tab;
    }

    protected function actionAjouterSucces(mixed ...$donnees) : JsonResponse
    {
        $data = $this->form->getData();
        if($this->repository->saisonEquipeExiste(id_saison: $data->getSaison()->getId(), id_equipe: ($donnees['donnees'][1])->getId()))
        {
            return new JsonResponse(data: [
                'code' => self::EXCEPTION,
                'exception' => "Cette équipe est déjà associée à cette saison"
            ]);
        }

        $data->setEquipe(equipe: $donnees['donnees'][1]);
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
            return $this->actionModifierSucces(objet: [$donnees[1],$donnees[2]]);                 

        }else{
            return $this->actionModifierEchec(erreurs: Utilitaire::getErreur(form: $this->form));
        }
    }

    public function actionModifierSucces(mixed $objet = null): JsonResponse
    {
        $doublon = $this->repository->saisonEquipeExiste(id_saison: ($objet[0])->getSaison()->getId(), id_equipe: ($objet[0])->getEquipe()->getId());
        if($doublon and $objet[1] != $doublon)
        {
            return new JsonResponse(data: [
                'code' => self::EXCEPTION,
                'exception' => "Cette équipe est déjà associée à cette saison"
            ]);
        }

        $this->em->flush();
        return new JsonResponse(data: ['code' => self::SUCCES]);
    }
}