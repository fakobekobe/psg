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
    public const VICTOIRE = 1; 
    public const NUL = 2; 
    public const DEFAITE = 3; 
    public const POINT_VICTOIRE = 3; 
    public const POINT_NUL = 1; 

    public const D1_2M = "D1_2M"; // Première mi-temps équipe à domicile, les deux équipes marquent
    public const E1_2M = "E1_2M"; // Première mi-temps équipe à l'extérieur, les deux équipes marquent
    public const N1_2M = "N1_2M"; // Première mi-temps, les deux équipes marquent
    public const P_D1_2M = "P_D1_2M"; // Pourcentage première mi-temps équipe à domicile, les deux équipes marquent
    public const P_E1_2M = "P_E1_2M"; // Pourcentage première mi-temps équipe à l'extérieur, les deux équipes marquent
    public const P_N1_2M = "P_N1_2M"; // Pourcentage première mi-temps, les deux équipes marquent
    public const P_PARI1_2M = "[80-100%:1-2M]"; // Pourcentage pari première mi-temps, les deux équipes marquent
    public const D1_1M = "D1_1M"; // Première mi-temps équipe à domicile, une seule équipe marque
    public const E1_1M = "E1_1M"; // Première mi-temps équipe à l'extérieur, une seule équipe marque
    public const P_D1_1M = "P_D1_1M"; // Pourcentage première mi-temps équipe à domicile, une seule équipe marque
    public const P_E1_1M = "P_E1_1M"; // Pourcentage première mi-temps équipe à l'extérieur, une seule équipe marque
    public const P_PARI1_1M = "[80-100%:1-1M]"; // Pourcentage pari première mi-temps, une seule équipe marque
    public const PB1 = "PB1"; // Première mi-temps pas de but
    public const P_PB1 = "P_PB1"; // Pourcentage première mi-temps pas de but
    public const P_PARI1_PB = "[80-100%:1-PB]"; // Pourcentage pari première mi-temps, pas de but
    public const P2M = "P2M"; // Les deux équipes marquent à la première mi-temps
    public const P_P2M = "%P2M"; // Pourcentage les deux équipes marquent à la première mi-temps
    public const P1M = "P1M"; // Une seule équipe marque à la première mi-temps
    public const P_P1M = "%P1M"; // Pourcentage une seule équipe marque à la première mi-temps
    public const P_PARI1_P2M = "[80-100%:1-P2M]"; // Pourcentage pari les deux équipes marquent à la première mi-temps
    public const P_PARI1_P1M = "[80-100%:1-P1M]"; // Pourcentage pari une seule équipe marque à la première mi-temps


    public const D2_2M = "D2_2M"; // Deuxième mi-temps équipe à domicile, les deux équipes marquent
    public const E2_2M = "E2_2M"; // Deuxième mi-temps équipe à l'extérieur, les deux équipes marquent
    public const N2_2M = "N2_2M"; // Deuxième mi-temps nul, les deux équipes marquent
    public const P_D2_2M = "P_D2_2M"; // Pourcentage deuxième mi-temps équipe à domicile, les deux équipes marquent
    public const P_E2_2M = "P_E2_2M"; // Pourcentage deuxième mi-temps équipe à l'extérieur, les deux équipes marquent
    public const P_N2_2M = "P_N2_2M"; // Pourcentage deuxième mi-temps nul, les deux équipes marquent
    public const P_PARI2_2M = "[80-100%:2-2M]"; // Pourcentage pari deuxième mi-temps, les deux équipes marquent
    public const D2_1M = "D2_1M"; // Deuxième mi-temps équipe à domicile, une seule équipe marque
    public const E2_1M = "E2_1M"; // Deuxième mi-temps équipe à l'extérieur, une seule équipe marque
    public const P_D2_1M = "P_D2_1M"; // Pourcentage deuxième mi-temps équipe à domicile, une seule équipe marque
    public const P_E2_1M = "P_E2_1M"; // Pourcentage deuxième mi-temps équipe à l'extérieur, une seule équipe marque
    public const P_PARI2_1M = "[80-100%:2-1M]"; // Pourcentage pari deuxième mi-temps, une seule équipe marque
    public const PB2 = "PB2"; // Deuxième mi-temps pas de but
    public const P_PB2 = "P_PB2"; // Pourcentage deuxième mi-temps pas de but
    public const P_PARI2_PB = "[80-100%:2-PB]"; // Pourcentage pari deuxième mi-temps, pas de but
    public const D2M = "D2M"; // Les deux équipes marquent à la deuxième mi-temps
    public const P_D2M = "%D2M"; // Pourcentage les deux équipes marquent à la deuxième mi-temps
    public const D1M = "D1M"; // Une seule équipe marque à la deuxième mi-temps
    public const P_D1M = "%D1M"; // Pourcentage une seule équipe marque à la deuxième mi-temps
    public const P_PARI2_D2M = "[80-100%:2-D2M]"; // Pourcentage pari les deux équipes marquent à la deuxième mi-temps
    public const P_PARI2_D1M = "[80-100%:2-D1M]"; // Pourcentage pari une seule équipe marque à la deuxième mi-temps

    
    public const TR = "TR"; 
    public const P_PARI = "[80-100%]"; 
    public const PARI = 80; 

    public const NPM = "NPM"; // Nul première mi-temps
    public const BPM = "BPM"; // But première mi-temps
    public const N2M = "N2M"; // Nul deuxème mi-temps
    public const B2M = "B2M"; // But deuxème mi-temps
    public const PNPM = "%NPM";
    public const PBPM = "%BPM"; 
    public const PN2M = "%N2M"; 
    public const PB2M = "%B2M"; 
    public const P_PARI_PM = "[80-100%:PM]"; // Pourcentage de pari première mi-temps
    public const P_PARI_2M = "[80-100%:2M]"; // Pourcentage de pari deuxième mi-temps

    public const D1 = "D1"; // Equipe à domicile première mi-temps
    public const E1 = "E1"; // Equipe à l'extérieur première mi-temps
    public const N1 = "N1"; // Nul première mi-temps
    public const PD1 = "%D1"; // Pourcentage Equipe à domicile première mi-temps
    public const PE1 = "%E1"; // Pourcentage Equipe à l'extérieur première mi-temps
    public const PN1 = "%N1"; // Pourcentage Nul première mi-temps
    public const P_PARI1 = "[80-100%:1]"; // Pourcentage de pari première mi-temps

    public const D2 = "D2"; // Equipe à domicile deuxième mi-temps
    public const E2 = "E2"; // Equipe à l'extérieur deuxième mi-temps
    public const N2 = "N2"; // Nul deuxième mi-temps
    public const PD2 = "%D2"; // Pourcentage Equipe à domicile deuxième mi-temps
    public const PE2 = "%E2"; // Pourcentage Equipe à l'extérieur deuxième mi-temps
    public const PN2 = "%N2"; // Pourcentage Nul deuxième mi-temps
    public const P_PARI2 = "[80-100%:2]"; // Pourcentage de pari deuxième mi-temps

    public const D3 = "D3"; // Equipe à domicile du match
    public const E3 = "E3"; // Equipe à l'extérieur du match
    public const N3 = "N3"; // Nul du match
    public const PD3 = "%D3"; // Pourcentage Equipe à domicile du match
    public const PE3 = "%E3"; // Pourcentage Equipe à l'extérieur du match
    public const PN3 = "%N3"; // Pourcentage Nul du match
    public const P_PARI3 = "[80-100%:3]"; // Pourcentage de pari du match

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

    public static function telecharger_fichier(string $id, string $url = "joueur", string $texte = "", string $taille = "h1") : string
    {
        return <<<HTML
        <a href="/{$url}/telecharger/{$id}" class="text-info {$taille}" title="Télécharger le fichier"><i class="typcn typcn-folder"></i> {$texte}</a>
HTML; 
    }

    public static function afficher_image(string $id, string $url = "joueur", string $path = "") : string
    {
        return <<<HTML
        <a href="/{$url}/telecharger/{$id}" class="text-info" title="Télécharger l'image">
            <img src="/images/{$path}" class="img_80" />
        </a>
HTML; 
    }

    public static function afficher_image_circulaire(?string $path = "", string $class ="img_30") : string
    {
        $path = $path ?? self::logo_defaut();
        return <<<HTML
        <img src="/images/{$path}" class="{$class}" />
HTML; 
    }

    public static function checkbox_rencontre(array $datas) : string
    {
        $retour = "";
        $cpt = 1;
        $total = count(value: $datas);

        foreach($datas as $data)
        {
            $retour .= <<<HTML
            <div class="row pt-2">
                <div class="col-lg-4">
                    <div class="form-group m-0 p-0">
                        <div class="custom-control custom-checkbox small">
                            <input type="radio" class="custom-control-input rencontre" name="rencontre" id="check{$data->getId()}" value="{$data->getId()}">
                            <label class="custom-control-label" for="check{$data->getId()}">
                                {$data->getCalendrier()->getJournee()->getDescription()}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    {$data->getDate()}
                </div>
                <div class="col-lg-2 text-center">
                    {$data->getHeure()}
                </div>
                <div class="col-lg-2 text-center">
                    {$data->getTemperatureAfficher()}
                </div>
            </div>            
HTML;
            ($cpt == $total) ? '' : $retour .= '<hr class="m-0 p-0" />';
            $cpt++;
        }

        return $retour;        
    }

    public static function checkbox_club(array $datas, string $name) : string
    {
        $retour = "";
        $cpt = 1;
        $total = count(value: $datas);
        $logo = "";

        foreach($datas as $data)
        {
            $logo = Utilitaire::afficher_image_circulaire(path: $data->getEquipe()->getLogo());

            $retour .= <<<HTML
            <div class="row pt-2">
                <div class="col-lg-6">
                    <div class="form-group m-0 p-0">
                        <div class="custom-control custom-checkbox small">
                            <input type="radio" class="custom-control-input {$name}" name="{$name}" id="check{$name}{$cpt}" value="{$data->getId()}">
                            <label class="custom-control-label" for="check{$name}{$cpt}">
                                {$data->getEquipe()->getNom()}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    {$logo}
                </div>
            </div>            
HTML;
            ($cpt == $total) ? '' : $retour .= '<hr class="m-0 p-0" />';
            $cpt++;
        }

        return $retour;        
    }

    private static function logo_defaut() : string
    {
        return "logo_defaut.png";
    }

    /**
     * tableau_portion est une fonction qui reçoit un tableau et renvoie un tableau selon le nombre
     * d'éléments donnés en paramètre
     * @param array $donnees est le tableau source
     * @param int $nombre est le nombre d'élément à retourner
     * @param bool $sens est un booléan qui définit le sens de parcourt. Par défaut il vaut false
     * c'est à dire que le parcourt se fait à la fin du tableau
     * @return array est le tableau de retour
     */
    public static function tableau_portion(array $donnees, int $nombre, bool $sens = false): array
    {
        $total = count(value: $donnees); 

        if($nombre >= $total) return $donnees;

        $debut = $sens ? 0 : ($total - $nombre);
        $fin = $sens ? $nombre  : $total;
        $retour = [];
        for($i = $debut; $i < $fin; $i++)
        {
            $retour[] = $donnees[$i];
        }

        return $retour;
    }

    public static function categorie_classement(int $rang_domicile, int $rang_exterieur): string
    {
        $groupe_domicile = Utilitaire::classement(rang: $rang_domicile);
        $groupe_exterieur = Utilitaire::classement(rang: $rang_exterieur);        

        return $groupe_domicile . "-" . $groupe_exterieur;
    }

    public static function classement(int $rang) : string
    {
        $groupe = "";
        switch(true)
        {
            case in_array(needle: $rang, haystack: range(start: 1,end: 5)): $groupe = "1"; break;
            case in_array(needle: $rang, haystack: range(start: 6,end: 10)): $groupe = "2"; break;
            case in_array(needle: $rang, haystack: range(start: 11,end: 15)): $groupe = "3"; break;
            case in_array(needle: $rang, haystack: range(start: 15,end: 20)): $groupe = "4"; break;
        }
        return $groupe;
    }

}