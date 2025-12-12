<?php

namespace App\Traitement\Model;

use App\Traitement\Abstrait\TraitementAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TraitementMatchDispute extends TraitementAbstrait
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
        $objet['id'] = ($donnees[0])->getId();
        $objet['saison'] = ($donnees[0])->getEquipeSaison()->getSaison()->getId();
        $objet['championnat'] = ($donnees[0])->getEquipeSaison()->getEquipe()->getChampionnat()->getId();
        $objet['joueur'] = ($donnees[0])->getJoueur()->getId();
        $liste = $this->repository->getListeEquipes(id_championnat: $objet['championnat']);
        $objet['equipe'] = $liste ? Utilitaire::getOptionsSelect(objet: $liste, label: 'Equipe', index: ($donnees[0])->getEquipeSaison()->getEquipe()->getId()) : "<option value=\"\">--- Equipe ---</option>";
        return $objet;
    }

    protected function chaine_data(mixed ...$donnees): string
    {
        // Les variables à charger avec les valeurs de la configuration depuis le repository
        $id_preponderance_domicile = 1;
        $id_preponderance_exterieur = 2;

        $tab = "";
        $i = 0;
        $separateur = ';x;';
        $nb = (int)(count(value: $donnees[0]) / 2);
        $id_rencontre = 0;
        $journee = "";
        $club_domicile = "";
        $club_exterieur = "";
        $date = "";
        $heure = "";
        $temperature = "";

        foreach ($donnees[0] as $data) {

            // On récupère le club à domicile
            if ($data->getPreponderance()->getId() == $id_preponderance_domicile) {

                $id_rencontre = $data->getRencontre()->getId();
                $journee = $data->getRencontre()->getCalendrier()->getJournee()->getDescription();
                $club_domicile = ucfirst(string: htmlspecialchars(string: $data->getEquipeSaison()->getEquipe()->getNom()));
                $date = $data->getRencontre()->getDate();
                $heure = $data->getRencontre()->getHeure();
                $temperature = $data->getRencontre()->getTemperatureAfficher();

                // On récupère le club à l'extérieur
                foreach ($donnees[0] as $data_exterieur) {
                    if (
                        $data_exterieur->getRencontre()->getId() == $id_rencontre and
                        $data_exterieur->getPreponderance()->getId() == $id_preponderance_exterieur
                    ) {
                        $club_exterieur = ucfirst(string: htmlspecialchars(string: $data_exterieur->getEquipeSaison()->getEquipe()->getNom()));
                    }
                }

                $nom = $journee . ' => ' . $club_domicile . ' VS ' . $club_exterieur . ' => ' . $date;

                $i++;
                $v = ($i != $nb) ? '!x!' : '';
                $tab .=  $i . $separateur .
                    $journee . $separateur .
                    $club_domicile . $separateur .
                    $club_exterieur . $separateur .
                    $date . $separateur .
                    $heure . $separateur .
                    $temperature . $separateur .
                    $this->lien_a(id: $data->getId(), nom: $nom) . $v;
            }
        }
        return $tab;
    }

    protected function lien_a(int $id, string $nom): string
    {
        return <<<HTML
    <div class="d-sm-inline-flex">
        <a href="#" class="text-white mr-1 text-success statBtn h1" title="Statistiques" data-id="$id"><i class="typcn typcn-chart-bar"></i></a>
        <a href="#" class="text-white text-danger deleteBtn h1" title="Supprimer" data-id="$id" data-nom="$nom"><i class="typcn typcn-trash"></i></a>
    </div>
HTML;
    }

    protected function actionAjouterSucces(mixed ...$donnees): JsonResponse
    {
        // Les variables à charger avec les valeurs de la configuration depuis le repository
        $id_preponderance_domicile = 1;
        $id_preponderance_exterieur = 2;

        $id_rencontre = $donnees['donnees'][1]['id_rencontre'];
        $id_domicile = $donnees['donnees'][1]['id_domicile'];
        $id_exterieur = $donnees['donnees'][1]['id_exterieur'];

        // On récupère la rencontre
        $rencontre = $this->repository->getRencontre(id_rencontre: $id_rencontre);

        // On récupère le club à domicile
        $club_domicile = $this->repository->getClub(id_club: $id_domicile);

        // On récupère le club à l'extérieur
        $club_exterieur = $this->repository->getClub(id_club: $id_exterieur);

        // On récupère la prépondérance à domicile
        $preponderance_domicile = $this->repository->getPreponderance(id_preponderance: $id_preponderance_domicile);

        // On récupère la prépondérance à l'extérieur
        $preponderance_exterieur = $this->repository->getPreponderance(id_preponderance: $id_preponderance_exterieur);

        /*
         * On vérifie si les 2 clubs (selon le championnat) n'ont pas été déjà enregistré dans la saison
         * soit à domicile vs extérieur
         */
        $liste_matchs = $this->repository->findMatchDisputeBySaisonByChampionnat(
            $club_domicile->getSaison()->getId(),
            $rencontre->getCalendrier()->getChampionnat()->getId()
        );

        foreach ($liste_matchs as $match) {
            if (
                $match->getEquipeSaison()->getId() == $id_domicile and
                $match->getPreponderance()->getId() == $id_preponderance_domicile
            ) {
                $id_rencontre_dispute = $match->getRencontre()->getId();

                // On vérifie si le club à l'exitérieur existe
                foreach ($liste_matchs as $match_exterieur) {
                    if (
                        $match_exterieur->getRencontre()->getId() == $id_rencontre_dispute and
                        $match_exterieur->getEquipeSaison()->getId() == $id_exterieur and
                        $match_exterieur->getPreponderance()->getId() == $id_preponderance_exterieur
                    ) {
                        return new JsonResponse(data: [
                            'code' => self::ECHEC,
                            'erreur' => "Cette rencontre existe déjà."
                        ]);
                    }
                }
                // On sort de la boucle
                break;
            }
        }

        /*
         * On vérifie le club a déjà un match selon le calendrier à domicile
         */
        if ($this->repository->findMatchByCalendrierByCubByPreponderance(
            $rencontre->getCalendrier()->getId(),
            $id_domicile,
            $id_preponderance_domicile
        )) {
            return new JsonResponse(data: [
                'code' => self::ECHEC,
                'erreur' => "Le club [" . $club_domicile->getEquipe()->getNom() . "] a déjà un match à [domicile] dans la [" . $rencontre->getCalendrier()->getJournee()->getDescription() . "]"
            ]);
        }

        // On ajoute le match à domicile
        $match_domicile = $this->repository->new();
        $match_domicile->setRencontre($rencontre);
        $match_domicile->setEquipeSaison($club_domicile);
        $match_domicile->setPreponderance($preponderance_domicile);
        $this->em->persist(object: $match_domicile);

        // On ajoute le match à l'extérieur
        $match_exterieur = $this->repository->new();
        $match_exterieur->setRencontre($rencontre);
        $match_exterieur->setEquipeSaison($club_exterieur);
        $match_exterieur->setPreponderance($preponderance_exterieur);

        $this->em->persist(object: $match_exterieur);
        $this->em->flush();

        // On récupère la liste des rencontres pour l'affichage dans le contenu_rencontre
        $d_rencontre = $this->repository->getRencontre($id_rencontre);
        $liste_rencontres = $this->liste_rencontre($this->repository, $d_rencontre->getCalendrier()->getId());

        return new JsonResponse(data: [
            'code' => self::SUCCES,
            'data' => Utilitaire::checkbox_rencontre(datas: $liste_rencontres[1]),
        ]);
    }

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

    public function actionSupprimer(mixed ...$donnees): JsonResponse
    {
        $objet = $this->repository->findOneBy(criteria: ['id' => $donnees[0]]);
        if ($objet !== null) {
            return $this->actionSupprimerSucces(objet: $objet);
        } else {
            return $this->actionSupprimerEchec();
        }
    }

    protected function actionSupprimerSucces(mixed $objet): JsonResponse
    {
        // On récupère les match selon la rencontre
        $liste_match = $this->repository->findBy(criteria: ['rencontre' => $objet->getRencontre()->getId()]);

        foreach ($liste_match as $match) {
            $this->em->remove(object: $match);
        }
        $this->em->flush();

        // On récupère la liste des rencontres pour l'affichage dans le contenu_rencontre
        $id_calendrier = $objet->getRencontre()->getCalendrier()->getId();
        $liste_rencontres = $this->liste_rencontre($this->repository, $id_calendrier);

        $data = [
            'message' => 'Votre données a bien été supprimée.',
            'html' => Utilitaire::checkbox_rencontre(datas: $liste_rencontres[1]),
        ];

        return new JsonResponse(data: [
            'code' => self::SUCCES,
            'data' => $data,
        ]);
    }

    private function liste_rencontre(mixed ...$donnees): array
    {
        $retour = [];

        $liste_rencontres = ($donnees[0])->getListeRencontres(id_calendrier: $donnees[1]);
        $liste_matchs = ($donnees[0])->findMatchByCalendrier(id_calendrier: $donnees[1]);
        $t_rencontres = [];
        $r_trouve = false;

        // On conserve les rencontres qui n'ont fait l'objet de match
        foreach ($liste_rencontres as $rencontre) {
            $r_trouve = false;
            foreach ($liste_matchs as $match) {
                if ($rencontre->getId() == $match->getRencontre()->getId()) {
                    $r_trouve = true;
                    break;
                }
            }

            // On conserve la rencontre
            if (!$r_trouve) {
                $t_rencontres[] = $rencontre;
            }
        }

        $retour = [
            0 => $liste_rencontres,
            1 => $t_rencontres,
        ];

        return $retour;
    }
}
