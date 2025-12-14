<?php

namespace App\Traitement\Model;

use App\Traitement\Abstrait\TraitementAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TraitementStatistique extends TraitementAbstrait
{
    public function __construct(
        protected ?EntityManagerInterface $em = null,
        protected ?FormInterface $form = null,
        protected ?ServiceEntityRepository $repository = null

    ) {
        parent::__construct(em: $this->em, form: $this->form, repository: $this->repository);
    }

    protected function getObjet(mixed ...$donnees): array
    {
        // Les variables à charger avec les valeurs de la configuration depuis le repository
        $id_preponderance_domicile = 1;
        $id_preponderance_exterieur = 2;

        foreach($donnees[0] as $statistique)
        {
            if($statistique->getMatchDispute()->getPreponderance()->getId() == $id_preponderance_domicile)
            {
                $objet['periode'] = $statistique->getPeriode()->getId();
                $objet['score_d'] = $statistique->getScore();
                $objet['possession_d'] = $statistique->getPossession();
                $objet['total_tir_d'] = $statistique->getTotalTir();
                $objet['tir_cadre_d'] = $statistique->getTirCadre();
                $objet['grosse_chance_d'] = $statistique->getGrosseChance();
                $objet['corner_d'] = $statistique->getCorner();
                $objet['carton_jaune_d'] = $statistique->getCartonJaune();
                $objet['carton_rouge_d'] = $statistique->getCartonRouge();
                $objet['hors_jeu_d'] = $statistique->getHorsJeu();
                $objet['coup_franc_d'] = $statistique->getCoupsFranc();
                $objet['touche_d'] = $statistique->getTouche();
                $objet['faute_d'] = $statistique->getFaute();
                $objet['tacle_d'] = $statistique->getTacle();
                $objet['arret_d'] = $statistique->getArret();


            }else if($statistique->getMatchDispute()->getPreponderance()->getId() == $id_preponderance_exterieur)
            {
                $objet['rencontre'] = $statistique->getMatchDispute()->getRencontre()->getId();
                $objet['periode'] = $statistique->getPeriode()->getId();
                $objet['score_e'] = $statistique->getScore();
                $objet['possession_e'] = $statistique->getPossession();
                $objet['total_tir_e'] = $statistique->getTotalTir();
                $objet['tir_cadre_e'] = $statistique->getTirCadre();
                $objet['grosse_chance_e'] = $statistique->getGrosseChance();
                $objet['corner_e'] = $statistique->getCorner();
                $objet['carton_jaune_e'] = $statistique->getCartonJaune();
                $objet['carton_rouge_e'] = $statistique->getCartonRouge();
                $objet['hors_jeu_e'] = $statistique->getHorsJeu();
                $objet['coup_franc_e'] = $statistique->getCoupsFranc();
                $objet['touche_e'] = $statistique->getTouche();
                $objet['faute_e'] = $statistique->getFaute();
                $objet['tacle_e'] = $statistique->getTacle();
                $objet['arret_e'] = $statistique->getArret();
            }
        }        
        return $objet;
    }

    protected function chaine_data(mixed ...$donnees): string
    {
        $tab = "";
        $i = 0;
        $separateur = ';x;';
        $nb = count(value: $donnees[0]);

        foreach ($donnees[0] as $data) {

            $calendrier = ($data['domicile'])->getMatchDispute()->getRencontre()->getCalendrier()->getJournee()->getDescription();
            $club_domicile = ($data['domicile'])->getMatchDispute()->getEquipeSaison()->getEquipe()->getNom();
            $club_exterieur  = ($data['exterieur'])->getMatchDispute()->getEquipeSaison()->getEquipe()->getNom();
            $periode = ($data['exterieur'])->getPeriode()->getLibelle();
            $score = ($data['domicile'])->getScore() . ' - ' . ($data['exterieur'])->getScore();

            $nom = $calendrier . ' => ' . $club_domicile . ' VS ' . $club_exterieur . ' => ' . $periode . ' => ' . $score;

            $i++;
            $v = ($i != $nb) ? '!x!' : '';
            $tab .=  $i . $separateur .
                $calendrier . $separateur .
                $club_domicile . $separateur .
                $club_exterieur . $separateur .
                $periode . $separateur .
                $score . $separateur .
                $this->lien_a(($data['domicile'])->getMatchDispute()->getRencontre()->getId(), ($data['exterieur'])->getPeriode()->getId(), $nom) . $v;
        }
        return $tab;
    }

    protected function lien_a(mixed ...$donnees): string
    {
        return <<<HTML
    <div class="d-sm-inline-flex">
        <a href="#" class="text-white mr-1 text-success editStatBtn h1" title="Modifier" data-id_rencontre="{$donnees[0]}" data-id_periode="{$donnees[1]}"><i class="typcn typcn-edit"></i></a>
        <a href="#" class="text-white text-danger deleteStatBtn h1" title="Supprimer" data-id_rencontre="{$donnees[0]}" data-id_periode="{$donnees[1]}" data-nom="{$donnees[2]}"><i class="typcn typcn-trash"></i></a>
    </div>
HTML;
    }
/*
    public function actionModifier(mixed ...$donnees): JsonResponse
    {
        if ($donnees[0]) {
            return $this->actionModifierSucces(objet: [$donnees[1], $donnees[2]]);
        } else {
            return $this->actionModifierEchec(erreurs: Utilitaire::getErreur(form: $this->form));
        }
    }

    public function actionModifierSucces(mixed $objet = null): JsonResponse
    {
        $saisonJoueur = $this->repository->findSaisonJoueur(($objet[0])->getEquipeSaison()->getSaison()->getId(), ($objet[0])->getJoueur()->getId());
        if ($saisonJoueur and $objet[1] != $saisonJoueur) {
            return new JsonResponse(data: [
                'code' => self::EXCEPTION,
                'exception' => "Ce joueur est déjà transféré dans un club"
            ]);
        }

        $this->em->flush();
        return new JsonResponse(data: ['code' => self::SUCCES]);
    }
*/

    public function actionCheck(mixed ...$donnees) : JsonResponse
    {
        $entity = $this->repository->findStatistiqueByRencontreByPeriode($donnees[0], $donnees[1]);
        if($entity !== null)
        {
            return $this->actionCheckSucces($entity);
        }else{
            return $this->actionCheckEchec();
        }
    }

    public function actionSupprimer(mixed ...$donnees): JsonResponse
    {
        $objet = $this->repository->findStatistiqueByRencontreByPeriode($donnees[0], $donnees[1]);
        if ($objet !== null) {
            return $this->actionSupprimerSucces(objet: $objet);
        } else {
            return $this->actionSupprimerEchec();
        }
    }

    protected function actionSupprimerSucces(mixed $objet): JsonResponse
    {
        foreach ($objet as $statistique) {
            $this->em->remove(object: $statistique);
        }
        $this->em->flush();        

        return new JsonResponse(data: [
            'code' => self::SUCCES,
            'message' => "Suppression effectuée avec succes."
        ]);
    }

}
