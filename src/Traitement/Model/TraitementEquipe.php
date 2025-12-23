<?php

namespace App\Traitement\Model;

use App\Traitement\Abstrait\TraitementAbstrait;
use App\Traitement\Utilitaire\Utilitaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class TraitementEquipe extends TraitementAbstrait
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
        $objet['nom'] = ($donnees[0])->getNom();
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
            $Championnat = strtoupper(string: htmlspecialchars(string: $data->getChampionnat()->getNom()));
            $nom = strtoupper(string: htmlspecialchars(string: $data->getNom()));
            $photo = $data->getLogo() ? Utilitaire::afficher_image(id: $data->getId(), url: "equipe", path: $data->getLogo()) : '';
            
            $i++;
            $v = ($i != $nb) ? '!x!':'';
            $tab .=  $i . $separateur . 
            $Championnat . $separateur . 
            $nom . $separateur . 
            $photo . $separateur . 
            $this->lien_a($data->getId(), $nom) . $v;
        }

        return $tab;
    }

    protected function actionAjouterSucces(mixed ...$donnees): JsonResponse
    {
        // On récupère l'objet
        $data = $this->form->getData();

        // On lève une exception au cas ou il n'y a pas de fichier uploader
        if ($donnees['donnees'][1]['image']) {
            // On sauvegarde le fichier , $donnees['donnees'][1]['image'] est l'objet image
            $nomDuFichier = Utilitaire::verifier_images(image: $donnees['donnees'][1]['image'], path: Utilitaire::getPathImage(), extensions: Utilitaire::EXTENSIONS_IMAGES, taille: Utilitaire::TAILLE_IMAGE);
            $data->setLogo(logo: $nomDuFichier);
        }       

        
        $this->em->persist(object: $data);
        $this->em->flush();

        return new JsonResponse(data: [
            'code' => self::SUCCES,
        ]);
    }

    public function actionModifier(mixed ...$donnees): JsonResponse
    {
        if ($donnees[0]) {
            return $this->actionModifierSucces(objet: $donnees[1]);
        } else {
            return $this->actionAjouterEchec(erreurs: Utilitaire::getErreur(form: $this->form));
        }
    }

    public function actionModifierSucces(mixed $objet = null): JsonResponse
    {
        // On vérifie si un fichier a été uploader, on suprime l'ancien et on sauvegarde le nouveau
        if ($objet['image']) {
            $data = $this->form->getData(); // On récupère l'objet cours
            $nomDuFichier = Utilitaire::verifier_images(image: $objet['image'], path: Utilitaire::getPathImage(), extensions: Utilitaire::EXTENSIONS_IMAGES, taille: Utilitaire::TAILLE_IMAGE);
            $path = Utilitaire::getPathImage() . DIRECTORY_SEPARATOR . $data->getLogo();
            Utilitaire::supprime_image(path: $path);
            $data->setLogo($nomDuFichier);            
        }

        $this->em->flush();

        return new JsonResponse(data: 
        ['code' => self::SUCCES,]);
    }

    protected function actionSupprimerSucces(mixed $objet): JsonResponse
    {
        $path = Utilitaire::getPathImage() . DIRECTORY_SEPARATOR . $objet->getLogo();
        Utilitaire::supprime_image(path: $path);
        $this->em->remove(object: $objet);
        $this->em->flush();
        return new JsonResponse(data: [
            'code' => self::SUCCES,
            'message' => "Votre données a bien été supprimée."
        ]);
    }
}