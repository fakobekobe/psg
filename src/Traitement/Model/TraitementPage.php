<?php

namespace App\Traitement\Model;

use App\Traitement\Abstrait\TraitementAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TraitementPage extends TraitementAbstrait
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

    protected function chaine_data(mixed ...$donnees): string
    {        
        $tab = "";
        $i = 0;
        $separateur = ';x;';
        $nb = count(value: $donnees[0]);

        foreach($donnees[0] as $data)
        {
            $nom = ucfirst(string: htmlspecialchars(string: $data->getNom()));

            $i++;
            $v = ($i != $nb) ? '!x!':'';
            $tab .=  $i . $separateur . 
            $nom . $separateur . $v;
        }

        return $tab;
    }

    protected function actionAjouterSucces(mixed ...$donnees) : JsonResponse
    {
        $pages = Utilitaire::getListePages();
        $cpt = 0;
        $i = 0;
        $message = "";
        $code = self::SUCCES;

        if($pages)
        {
            foreach($pages as $nom)
            {
                if(!$this->repository->findOneBy(criteria: ['nom' => $nom]))
                {
                    $objet = $this->repository->new();
                    $objet->setNom(nom: $nom);
                    $this->em->persist(object: $objet);
                    $this->em->flush();
                    $cpt++;
                }else{
                    $i++;
                }
            }

            if(!$cpt)
            {
                $code = static::ECHEC;
                $message = "$i pages non enregistrées car elles existent déjà.";
            }
        }        

        return new JsonResponse(data: [
            'code' => $code,
            'message' => $message
        ]);
    }
}