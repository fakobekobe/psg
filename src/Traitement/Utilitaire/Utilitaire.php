<?php

namespace App\Traitement\Utilitaire;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Form\FormInterface;
use App\Entity\User;
use ErrorException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class Utilitaire
{
    // Déclaration des constantes 
    public const ID_ANNEESCOLAIRE = 'id_anneescolaire';
    public const EXTENSIONS_IMAGES = ['jpg', 'jpeg', 'png', 'gif', 'tiff'];
    public const EXTENSIONS_FICHIERS = ['csv', 'txt'];
    public const EXTENSIONS_DONNEES = ['jpg', 'jpeg', 'png', 'gif', 'tiff', 'jfif', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'mp4', 'mp3', 'movie'];
    public const TAILLE_IMAGE = 10000000; // 10Mo
    public const TAILLE_FICHIER = 100000000; // 100Mo
    public const UPLOAD_ERR_OK = 0; 

    /**
     * Résumé de getErreur :
     * Cette méthode reçoit un objet de type FormInterface et retourne la liste des erreurs
     * de cet objet dans un tableau.
     * @param \Symfony\Component\Form\FormInterface $form
     * @return array[] Tableau des erreurs avec pour clé le nom de chaque champ
     */
    public static function getErreur(FormInterface $form): array
    {
        $erreurs = [];
        foreach ($form->all() as $champ) {
            if (!$champ->isValid()) {
                foreach ($champ->getErrors() as $erreur) {
                    $erreurs[$champ->getName()][] = $erreur->getMessage();
                }
            }
        }
        return $erreurs;
    }

    /**
     * getOptionsSelect permet la création des options d'un select
     * @param mixed $objet La liste des objets qui devrons faire l'objet des options
     * @param string $label Le nom de la propriété manipulé
     * @param mixed $index L'ID de l'option sélectionnée
     * @return string La liste des options
     */
    public static function getOptionsSelect(mixed $objet, string $label, ?int $index = null, ?string $libelle = 'libelle'): string
    {
        $options = "<option value=\"\">--- " . $label . " ---</option>";
        $selected = "";
        foreach ($objet as $o) {
            $selected = "";
            if ($index) if ($index == $o['id'])
                $selected = "selected";
            $value = ucfirst(string: $o[$libelle]);
            $options .= "<option value=\"{$o['id']}\" $selected>$value</option>";

        }

        return $options;
    }

    /**
     * getListePages permet le retourner la liste des controlleurs
     * @param mixed $dossier est le chemin du dossier Controller
     * @return string[] la liste des controlleurs sans le surfixe Controlleur
     */
    public static function getListePages(?string $dossier = null): array
    {
        $dossier = $dossier ?: dirname(path: __DIR__, levels: 2) . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR;
        $liste_pages = [];
        $dossiers = glob(pattern: $dossier . '*');

        if ($dossiers) {
            foreach ($dossiers as $file) {
                $files = explode(separator: DIRECTORY_SEPARATOR, string: $file);
                $file_extension = $files[count(value: $files) - 1];
                $nom_fichier = explode(separator: '.', string: $file_extension)[0];
                //$liste_pages[] = $nom_fichier;
                $liste_pages[] = str_replace(search: 'Controller', replace: '', subject: $nom_fichier);
            }
        }

        return $liste_pages;
    }

    public static function chaineFormulaire(array $tableau, string $class, string $nom, string $input_name, string $type, mixed $data = null, ?ServiceEntityRepository $objetRepository = null): string
    {
        $tab = '<div class="row"><div class="col-lg-4 col-sm-12">';
        $i = 1;
        $checked = '';
        $id_gerants = [];
        $id_pages = [];

        $objet = $data;

        if ($objet) {
            if ($type == 'gerant') {
                // Cas du gerant
                $gerants = $objetRepository->findBy(criteria: ['groupe' => $objet]);
                foreach ($gerants as $gerant) {
                    $id_gerants[] = $gerant->getUser()->getId();
                }
            } elseif ($type == 'page') {
                // Cas du gerant
                $pages = $objetRepository->findBy(criteria: ['groupe' => $objet[0]->getGroupe()->getId(), 'droit' => $objet[0]->getDroit()->getId()]);
                foreach ($pages as $page) {
                    $id_pages[] = $page->getPage()->getId();
                }
            }

        }

        foreach ($tableau as $data) {
            $valeur = htmlspecialchars(string: ucfirst(string: $data->$nom()));
            if ($objet) {
                if ($type == 'groupe') {
                    // Cas du groupe
                    $checked = ($data->getId() == (is_array(value: $objet) ? $objet[0]->getGroupe()->getId() : $objet)) ? 'checked' : '';
                } elseif ($type == 'gerant') {
                    // Cas du gerant
                    $checked = in_array(needle: $data->getId(), haystack: $id_gerants) ? 'checked' : '';
                } elseif ($type == 'droit') {
                    // Cas du droit
                    $checked = ($data->getId() == $objet[0]->getDroit()->getId()) ? 'checked' : '';
                } elseif ($type == 'page') {
                    // Cas du gerant
                    $checked = in_array(needle: $data->getId(), haystack: $id_pages) ? 'checked' : '';
                }

            }

            $reference = $input_name . $data->getId();
            $tab .= "<div class=\"form-group mb-3\">
                <div class=\"custom-control custom-checkbox small\">
                <input type=\"checkbox\" name=\"{$input_name}[]\" id=\"labelgroupe$reference\" class =\"custom-control-input $class\" value=\"{$data->getId()}\" $checked>
                <label for=\"labelgroupe$reference\" class=\"custom-control-label\">$valeur</label></div></div>
                ";

            if (($i % 9 == 0)) {
                $tab .= "</div></div>
                    <hr style=\"box-shadow:3px 3px rgba(0,0,0,0.1);\">
                    <div class=\"row\">
                    <div class=\"col-md-4 col-sm-12\">                    
                    ";
            } elseif (($i % 3 == 0)) {
                $tab .= "</div>
                    <div class=\"col-md-4 col-sm-12\">
                    ";
            }

            $i++;
        }

        $tab .= "</div></div>";
        return $tab;
    }

    /**
     * AjouteDroitUtilisateur permet d'ajouter des roles (droit des pages) aux utilisateurs
     * @param int $id_groupe Id du groupe
     * @param \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository $objetRepo
     * @param mixed $utilisateurs Tableau des utilisateurs
     * @return void
     */
    public static function AjouteDroitUtilisateur(int $id_groupe, ServiceEntityRepository $objetRepo, ?array $utilisateurs = null, ?User $utilisateurEnCours = null): void
    {
        // On récupère les utilisateurs du groupe au cas ou nous sommes au niveau de Droit par Groupe de Page
        if ($utilisateurs === null) {
            $utilisateurs = $objetRepo->getUtilisateursByGroupe(id_groupe: $id_groupe);
        }

        // On récupère la liste des droit par page selon le groupe
        $droitsPages = $objetRepo->getDroitsPagesByGroupe(id_groupe: $id_groupe);

        foreach ($utilisateurs as $user) {
            if(!empty($utilisateurEnCours) and $user->getId() == $utilisateurEnCours->getId()) continue; // Pour éviter les erreurs
            $roles = [];
            foreach ($droitsPages as $droit) {
                $droitpage = strtolower(string: $droit['droit'] . '_' . $droit['page']);
                if (!in_array(needle: $droitpage, haystack: $user->getRoles())) {
                    $roles[] = $droitpage;
                }
            }

            // On fusionne les roles
            $roles = array_merge($user->getRoles(), $roles);

            // On ajoute les roles de l'utilisateur
            $user->setRoles(roles: array_values(array: $roles));
            $objetRepo->getEntityManager()->flush();
        }
    }

    public static function SupprimeDroitUtilisateur(int $id_groupe, ServiceEntityRepository $objetRepo, ?array $utilisateurs = null, ?int $id_droit = null, ?User $utilisateurEnCours = null): void
    {
        // On récupère les utilisateurs du groupe au cas ou nous sommes au niveau de Droit par Groupe de Page
        if ($utilisateurs === null) {
            $utilisateurs = $objetRepo->getUtilisateursByGroupe(id_groupe: $id_groupe);
        }

        // On récupère la liste des droit par page selon le groupe
        $droitsPages = $objetRepo->getDroitsPagesByGroupe(id_groupe: $id_groupe, id_droit: $id_droit);
        $listeDroitpage = [];
        foreach ($droitsPages as $droit) 
        {
            $listeDroitpage[] = strtolower(string: $droit['droit'] . '_' . $droit['page']);
        }

        foreach ($utilisateurs as $user) {
            if(!empty($utilisateurEnCours) and $user->getId() == $utilisateurEnCours->getId()) continue;
            $roles = $user->getRoles();
            $copieRoles = $roles;

            for ($i = 0; $i < count(value: $roles); $i++) {
                if (in_array(needle: $roles[$i], haystack: $listeDroitpage)) {
                    unset($copieRoles[$i]);
                }
            }

            // On ajoute les roles de l'utilisateur
            $user->setRoles(roles: array_values(array: $copieRoles));

            // On effectue la modification
            $objetRepo->getEntityManager()->flush();
        }
    }

    // Gestion des images--------------------------
    /**
     * getPathImage permet de retourner le chemin absolue du fichier images qui sert de sauvegarde des images
     * @return string
     */
    public static function getPathImage() : string
    {
        return dirname(path: __DIR__,levels: 3) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images';
    }

    /**
     * verifier_images permet de vérifier et de sauvegarder un fichier
     * @param UploadedFile $image Fichier à sauvegarder
     * @param string $path Chemin du repertoir de sauvegarde du fichier
     * @param array $extensions Liste des extensions autorisées
     * @param int $taille Taille maximum autorisée
     * @return string|null Nouveau Nom du fichier à sauvegarder dans la base de données
     */
    public static function verifier_images(?UploadedFile $image, string $path, array $extensions,  int $taille) : ?string
    {
        if(!$image) return null;
        $fichier = '';
        $date = new \DateTime();

        if($image->isValid())
        {
            if($image->getError() === static::UPLOAD_ERR_OK)
            {
                //Pas d'erreur
                if($image->getSize() <= $taille)
                {
                    $date_i = $date->format(format: 'Ymd_Hisu');
                    $nom_fichier = pathinfo(path: $image->getClientOriginalName(), flags: PATHINFO_FILENAME);
                    $nom_extension = pathinfo(path: $image->getClientOriginalName(), flags: PATHINFO_EXTENSION);
                    if( in_array(needle: strtolower(string: $nom_extension), haystack: $extensions))
                    {
                        $fichier = $nom_fichier . '_'. $date_i .'.'. $nom_extension;
                        static::sauvegarde_images(nom_fichier: $fichier, image: $image->getPathname(), path: $path);
                    }
                }
            }
        }
        
        return $fichier ?: null;
    }

    /**
     * sauvegarde_images permet la sauvegarde physique du fichier
     * @param string $nom_fichier Nom du fichier à sauvegarder
     * @param string $image Chemin absolue du ficher temporaire
     * @param string $path Chemin absolue pour la sauvegarde du fichier
     * @return void
     */
    private static function sauvegarde_images(string $nom_fichier, string $image, string $path) : void
    {
        $nom = $path . DIRECTORY_SEPARATOR . $nom_fichier;
        move_uploaded_file(from: $image, to: $nom);
    }

    public static function supprime_image(string $path) : void
    {
        try{
            unlink(filename: $path);
        }catch(ErrorException $e){            
        }
    }

    //--------------------------------------------


    public static function chaine_tableau_js(array $tableau): string
    {        
        $tab = "";
        $i = 0;
        $separateur = ';x;';
        $nb = count(value: $tableau);

        foreach($tableau as $data)
        {
            $nomComplet = strtoupper(string: htmlspecialchars(string: $data['nomComplet']));
            $matricule = htmlspecialchars(string: $data['matricule']);
            $classe = htmlspecialchars(string: $data['classe']);
            $utilisateur = strtoupper(string: htmlspecialchars(string: $data['utilisateur']));
            $montant = number_format(
                    num: $data['montant'], 
                    decimals:0, 
                    decimal_separator:'', 
                    thousands_separator:' '
                );
            $date = ($data['dateVersement'])->format(format:'d/m/Y H:i:s');

            // Les données des versements
            $totalversement = "";
            $remise = "";
            $restepayer = "";

            if(!empty($data['scolariteTotal']))
            {
                $totalversement = number_format(
                    num: $data['scolariteTotal'], 
                    decimals:0, 
                    decimal_separator:'', 
                    thousands_separator:' '
                );

                $remise = number_format(
                    num: $data['remise'], 
                    decimals:0, 
                    decimal_separator:'', 
                    thousands_separator:' '
                );

                $restepayer = number_format(
                    num: $data['restePayer'], 
                    decimals:0, 
                    decimal_separator:'', 
                    thousands_separator:' '
                );

                $montant = number_format(
                    num: $data['totalVerse'], 
                    decimals:0, 
                    decimal_separator:'', 
                    thousands_separator:' '
                );
            }             

            $i++;
            $v = ($i != $nb) ? '!x!':'';
            $tab .=  $i . $separateur . 
            $nomComplet . $separateur . 
            $matricule . $separateur . 
            $classe . $separateur . 
            $montant . $separateur . 
            $restepayer . $separateur . 
            $remise . $separateur . 
            $totalversement . $separateur . 
            $date . $separateur . 
            $utilisateur . $v;
        }

        return $tab;
    }

    /**
     * csv_tableau permet de traitement un fichier csv pour retourner une liste de tableaux 
     * La première ligne du fichier csv sera supprimer si elle contient le titre
     * @param string $fichier Chemin du fichier
     * @param string $separateur Séparateur du csv
     * @param bool $titre si à true, elle permet la suppression de la première ligne du fichier. 
     * Par défaut titre = true.
     * @return string[][] Liste de tableau
     */
    public static function csv_tableau(string $fichier, string $separateur = ';', bool $titre = true) : array
    {
        $liste = [];
        $data = file(filename: $fichier);
        if($data)
        {
            // On supprime la première ligne qui contient le titre
            if($titre) unset($data[0]);
            foreach($data as $ligne)
            {
                $liste[] = explode(separator: $separateur, string: trim(string: $ligne));
            }
        }
        
        return $liste;
    }

    public static function verifier_fichier(?UploadedFile $fichier, array $extensions,  int $taille) : ?string
    {
        if(!$fichier) return null;
        $retour = null;

        if($fichier->isValid())
        {
            if($fichier->getError() === static::UPLOAD_ERR_OK)
            {
                //Pas d'erreur
                if($fichier->getSize() <= $taille)
                {
                    $nom_extension = pathinfo(path: $fichier->getClientOriginalName(), flags: PATHINFO_EXTENSION);
                    if( in_array(needle: strtolower(string: $nom_extension), haystack: $extensions))
                    {
                        $retour = $fichier->getPathname();
                    }
                }
            }
        }
        
        return $retour;
    }

    /**
     * tableau_unique permet de retourner un tableau unique sans doublons
     * @param array $data est le tableau source
     * @param string $key est la clé du tableau sur laquelle se fait la recherche unique
     * @return array[] le tableau retourné est un nouveau tableau des données sources sans doublons
     */

    public static function tableau_unique(array $data, string $key) : array
    {        
        $tid = [];
        $tindice = [];
        $cpt = 0;
        foreach($data as $value)
        {
            $tid[] = $value[$key];
            $tindice[] = $cpt;
            $cpt++;
        }

        $tdata[] = $data[0];
        $tkey[] = $tid[0];

        $cpt = 0;
        foreach($tid as $value)
        {
            $cle = array_search(needle: $value, haystack: $tkey);
            if($cle === false )
            {
                $tkey[] = $value;
                $tdata[] = $data[$tindice[$cpt]];
            }
            $cpt++;
        }

        return $tdata;
    }

    public static function telecharger_fichier(string $path, string $url = "cours", string $texte = "", string $taille = "h1") : string
    {
        return <<<HTML
        <a href="/{$url}/telecharger/{$path}" class="text-info {$taille}" title="Télécharger le fichier"><i class="typcn typcn-folder"></i> {$texte}</a>
HTML; 
    }

}