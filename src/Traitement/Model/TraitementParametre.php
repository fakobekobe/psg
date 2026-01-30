<?php

namespace App\Traitement\Model;

use App\Traitement\Abstrait\TraitementAbstrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TraitementParametre extends TraitementAbstrait
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
        return [];
    }

    protected function actionAjouterSucces(mixed ...$donnees) : JsonResponse
    {
        $data = $donnees['donnees'][1];
        $objet = $this->repository->new();
        $message = "Votre modification a été effectuée avec succès";

        $objet->setDomicile($data['domicile'] ?: 1);
        $objet->setExterieur($data['exterieur'] ?: 1);
        $objet->setPremiereMT($data['premiereMT'] ?: 1);
        $objet->setSecondeMT($data['secondeMT'] ?: 1);

        if(!$objet->getId())
        {
            $this->em->persist(object: $objet);
            $message = "Votre enregistrement a été effectué avec succès";
        }
        $this->em->flush();
        return new JsonResponse(data: [
            'code' => self::SUCCES,
            'message' => $message
        ]);
    }
}