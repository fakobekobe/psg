<?php

namespace App\Traitement\Model;

use App\Traitement\Abstrait\TraitementAbstrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TraitementCalendrier extends TraitementAbstrait
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
        $objet['championnat'] = ($donnees[0])->getChampionnat()->getId();
        $objet['journee'] = ($donnees[0])->getJournee()->getId();
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
            $championnat = ucfirst(string: htmlspecialchars(string: $data->getChampionnat()->getNom()));
            $journee = $data->getJournee()->getNumero();

            $nom = $championnat . ' => ' . $journee . ' journée';
            $i++;
            $v = ($i != $nb) ? '!x!':'';
            $tab .=  $i . $separateur . 
            $championnat . $separateur . 
            $journee . $separateur . 
            $this->lien_a($data->getId(), $nom) . $v;
        }

        return $tab;
    }

    protected function actionAjouterSucces(mixed ...$donnees) : JsonResponse
    {
        $objet = $this->form->getData();
        $id_championnat = $objet->getChampionnat()->getId();
        $championnat = $objet->getChampionnat();

        foreach($donnees['donnees'][1] as $id_journee)
        {
            if(!$this->repository->calendrierExiste(id_championnat: $id_championnat, id_journee: $id_journee))
            {
                $journee = $this->repository->getJournee($id_journee);
                if($journee)
                {
                    $calendrier = $this->repository->new();
                    $calendrier->setChampionnat($championnat);
                    $calendrier->setJournee($journee);
                    $this->em->persist(object: $calendrier);
                    $this->em->flush();
                }
            }
        }
        
        return new JsonResponse(data: [
            'code' => self::SUCCES,
        ]);
    }
}