<?php

namespace App\Traitement\Model;

use App\Traitement\Abstrait\TraitementAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TraitementTransfert extends TraitementAbstrait
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
        $objet['saison'] = ($donnees[0])->getEquipeSaison()->getSaison()->getId();
        $objet['championnat'] = ($donnees[0])->getEquipeSaison()->getEquipe()->getChampionnat()->getId();
        $objet['joueur'] = ($donnees[0])->getJoueur()->getId();
        $liste = $this->repository->getListeEquipes(id_championnat: $objet['championnat']);
        $objet['equipe'] = $liste ? Utilitaire::getOptionsSelect(objet:$liste, label: 'Equipe', index: ($donnees[0])->getEquipeSaison()->getEquipe()->getId()) : "<option value=\"\">--- Equipe ---</option>";
        return $objet;
    }

    protected function chaine_data(mixed ...$donnees): string
    {        
        $tab = "";
        $i = 0;
        $separateur = ';x;';
        $nb = count(value: $donnees[0]); 

        foreach($donnees[0] as $data)
        {
            $saison = ucfirst(string: htmlspecialchars(string: $data->getEquipeSaison()->getSaison()->getLibelle()));
            $championnat = ucfirst(string: htmlspecialchars(string: $data->getEquipeSaison()->getEquipe()->getChampionnat()->getNom()));
            $equipe = ucfirst(string: htmlspecialchars(string: $data->getEquipeSaison()->getEquipe()->getNom()));
            $joueur = ucfirst(string: htmlspecialchars(string: $data->getJoueur()->getNom()));

            $nom = $saison . ' => ' . $championnat . ' => ' . $equipe . ' => ' . $joueur;
            $i++;
            $v = ($i != $nb) ? '!x!':'';
            $tab .=  $i . $separateur . 
            $saison . $separateur . 
            $championnat . $separateur . 
            $equipe . $separateur . 
            $joueur . $separateur . 
            $this->lien_a(id: $data->getId(), nom: $nom) . $v;
        }

        return $tab;
    }

    protected function actionAjouterSucces(mixed ...$donnees) : JsonResponse
    {        
        if(!$donnees['donnees'][1])
        {
            return new JsonResponse(data: [
                'code' => self::EXCEPTION,
                'exception' => "Ce club n'existe pas!"
            ]);
        }

        $data = $this->form->getData();

        if($this->repository->getEquipeSaisonJoueur(($donnees['donnees'][1])->getId(), $data->getJoueur()->getId()))
        {
            return new JsonResponse(data: [
                'code' => self::EXCEPTION,
                'exception' => "Ce joueur est déjà transféré dans ce club"
            ]);
        }

        if($this->repository->findSaisonJoueur(($donnees['donnees'][1])->getSaison()->getId(), $data->getJoueur()->getId()))
        {
            return new JsonResponse(data: [
                'code' => self::EXCEPTION,
                'exception' => "Ce joueur est déjà transféré dans un club"
            ]);
        } 
              
        
        $data->setEquipeSaison($donnees['donnees'][1]);
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
            return $this->actionModifierSucces(objet: [$donnees[1], $donnees[2]]);                 

        }else{
            return $this->actionModifierEchec(erreurs: Utilitaire::getErreur(form: $this->form));
        }
    }

    public function actionModifierSucces(mixed $objet = null): JsonResponse
    {
        $saisonJoueur = $this->repository->findSaisonJoueur(($objet[0])->getEquipeSaison()->getSaison()->getId(), ($objet[0])->getJoueur()->getId());
        if($saisonJoueur and $objet[1] != $saisonJoueur)
        {
            return new JsonResponse(data: [
                'code' => self::EXCEPTION,
                'exception' => "Ce joueur est déjà transféré dans un club"
            ]);
        } 

        $this->em->flush();
        return new JsonResponse(data: ['code' => self::SUCCES]);
    }
}