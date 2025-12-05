<?php

namespace App\Traitement\Controlleur;

use App\Entity\Classe;
use App\Entity\Cours;
use App\Entity\DisciplineFiliere;
use DateTime;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Traitement\Utilitaire\Utilitaire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TraitementControlleur
{
    public function __construct(private ?FormFactoryInterface $formFactory = null) {}

    public function ajouter(
        Request $request,
        ServiceEntityRepository $repository,
        EntityManagerInterface $em,
        string $typeForm,
        mixed ...$donnees,
    ): array {
        $objet = $repository->new();
        $form = $this->formFactory->create(type: $typeForm, data: $objet, options: [
            'attr' => ['id' => $donnees['donnees'][0]], //$donnees['donnees'][0] = idForm = form_type
        ]);

        $form->handleRequest(request: $request);
        $retour['form'] = $form;

        if ($form->isSubmitted()) {
            $estValide = $form->isValid();
            $objet = null;

            // Gestion des cas avec fichier
            if ($donnees['donnees'][3] ?? null) {
                $objet = $donnees['donnees'][3];
                if ($form->get(name: 'titre')->getData()) {
                    $estValide = true;
                }
            } else {
                //Gestion du cas du select lorsque la variable $donnees existe ----------------------  
                if ($donnees['donnees'][2] ?? null) {
                    $erreur = Utilitaire::getErreur(form: $form);
                    if (count(value: $donnees['donnees'][2]) == 2) {
                        // Gestion d'un seul select
                        if (count(value: $erreur) <= 1 && $erreur[$donnees['donnees'][2]['libelle']] && $donnees['donnees'][2]['objet']) {
                            $estValide = true;
                            $objet = $donnees['donnees'][2]['objet'];
                        }
                    } else {
                        // Gestion de 4 select
                        if (count(value: $erreur) <= 4) {
                            $cpt = 0;
                            foreach ($donnees['donnees'][2] as $element) {
                                if ($element) {
                                    foreach ($erreur as $key => $value) {
                                        if ($element['libelle'] == $key) {
                                            $cpt++;
                                        }
                                    }
                                }
                            }

                            if ($cpt == 4) {
                                $estValide = true;
                                $objet['discipline'] = $donnees['donnees'][2][0]['objet'];
                                $objet['periode'] = $donnees['donnees'][2][1]['objet'];
                                $objet['classe'] = $donnees['donnees'][2][2]['objet'];
                                //$objet['filiere'] = $donnees['donnees'][2][3]['objet'];
                            }
                        }
                    }
                }
                //--------------------------------------------------------------------------------
            }

            // Initialisation du traitement
            $repository->initialiserTraitement(em: $em, form: $form, repository: $repository);

            /**  
             * On initialise la variable pour le hashage du mot de passe de l'utilisateur. 
             * Cas des entités ELeve, Professeur et Superviseur
             * $donnees['donnees'][0] = $userPasswordHasher ou null
             */
            $userPasswordHasher = $donnees['donnees'][1] ?? null;

            // Appel et récupération des données de la méthode du traitement
            $retour['reponse'] = ($repository->getTraitement())->actionAjouter($estValide, $userPasswordHasher, $objet);
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }

    public function ajouter_cours_classe(
        Request $request,
        ServiceEntityRepository $repository,
        EntityManagerInterface $em,
        string $typeForm,
        mixed ...$donnees,
    ): array {
        $objet = $repository->new();
        $form = $this->formFactory->create(type: $typeForm, data: $objet, options: [
            'attr' => ['id' => $donnees['donnees'][0]], //$donnees['donnees'][0] = idForm = form_type
        ]);

        $retour['form'] = $form;

        if ($request->isMethod(method: 'POST')) {

            $data = $request->request->all();
            $cours = $data['cours_classe']['cours'] ?? null;
            $classe = $data['cours_classe']['classe'] ?? null;
            $discipline = $data['cours_classe']['discipline'] ?? null;
            $estValide = false;

            if ($cours and $classe and $discipline) {
                // On récupère les objets
                $classe = $em->getRepository(className: Classe::class)->findOneBy(criteria: ['id' => (int)$classe]);
                $cours = $em->getRepository(className: Cours::class)->findOneBy(criteria: ['id' => (int)$cours]);
                $disciplineFiliere = $em->getRepository(className: DisciplineFiliere::class)->findOneBy(criteria: ['id' => (int)$discipline]);

                $objet->setCours($cours);
                $objet->setClasse($classe);
                $objet->setDisciplineFiliere($disciplineFiliere);
                $estValide = true;
            }

            // Initialisation du traitement
            $repository->initialiserTraitement(em: $em, form: $form, repository: $repository);

            // Appel et récupération des données de la méthode du traitement
            $retour['reponse'] = ($repository->getTraitement())->actionAjouter($estValide, null, $objet);
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }

    public function ajouter_questionnaire(
        Request $request,
        ServiceEntityRepository $repository,
        EntityManagerInterface $em,
        mixed ...$donnees,
    ): array {

        $objet_questionnaire = $repository->new();
        $form_questionnaire = $this->formFactory->create(type: $donnees['donnees'][0][0], data: $objet_questionnaire, options: [
            'attr' => ['id' => $donnees['donnees'][0][1]],
        ]);

        $objet_proposition = $repository->proposition();
        $form_proposition = $this->formFactory->create(type: $donnees['donnees'][1][0], data: $objet_proposition, options: [
            'attr' => ['id' => $donnees['donnees'][1][1], 'action' => $donnees['donnees'][1][2]],
        ]);

        $form_questionnaire->handleRequest(request: $request);
        $retour['form_questionnaire'] = $form_questionnaire;
        $retour['form_proposition'] = $form_proposition;

        $objet = null;

        if ($form_questionnaire->isSubmitted()) {
            $estValide = $form_questionnaire->isValid();

            //Gestion du cas du select lorsque la variable $donnees existe ----------------------  
            if ($donnees['donnees'][2] ?? null) {
                $erreur = Utilitaire::getErreur(form: $form_questionnaire);
                // Gestion d'un seul select
                if (count(value: $erreur) <= 1 && $erreur[$donnees['donnees'][2]['libelle']] && $donnees['donnees'][2]['objet']) {
                    $estValide = true;
                    $objet['objet'] = $donnees['donnees'][2]['objet'];
                    $objet['fichiers'] = null;

                    // On conserve les fichiers au cas ou ils existent
                    $fichiers = $request->files->all();
                    if ($fichiers['fichier'] ?? false) {
                        $objet['fichiers'] = $fichiers;
                    }
                }
            }

            // Initialisation du traitement
            $repository->initialiserTraitement(em: $em, form: $form_questionnaire, repository: $repository);

            // Appel et récupération des données de la méthode du traitement
            $retour['reponse'] = ($repository->getTraitement())->actionAjouter($estValide, $objet);
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }

    public function ajouter_contenu(
        Request $request,
        ServiceEntityRepository $repository,
        EntityManagerInterface $em,
        string $typeForm,
        mixed ...$donnees,
    ): JsonResponse {
        $objet = $repository->new();
        $form = $this->formFactory->create(type: $typeForm, data: $objet, options: [
            'attr' => ['id' => $donnees['donnees'][0]],
        ]);

        $id = ($request->request->all())[$donnees['donnees'][1]];
        $form->handleRequest(request: $request);
        $repository->initialiserTraitement(em: $em, form: $form, repository: $repository);

        // Appel et récupération des données de la méthode du traitement
        return ($repository->getTraitement())->actionAjouter($id);
    }

    public function ajouter_correction(
        Request $request,
        ServiceEntityRepository $repository,
        EntityManagerInterface $em,
        mixed ...$donnees,
    ): JsonResponse {

        $id = ($request->request->all())['id'];
        $repository->initialiserTraitement(em: $em, repository: $repository);

        // Appel et récupération des données de la méthode du traitement
        return ($repository->getTraitement())->actionAjouter($id);
    }

    public function ajouter_configuration(
        Request $request,
        ServiceEntityRepository $repository,
        EntityManagerInterface $em,
        string $typeForm,
        mixed ...$donnees,
    ): array {
        $objet = $repository->new();
        $form = $this->formFactory->create(type: $typeForm, data: $objet, options: [
            'attr' => ['id' => $donnees['donnees'][0]], //$donnees['donnees'][0] = idForm = form_type
        ]);

        $form->handleRequest(request: $request);
        $retour['form'] = $form;

        if ($form->isSubmitted()) {

            $objet = $form->getData();
            $estValide = true;
            if ($objet->getDenomination() === null or $objet->getSigle() === null) {
                $estValide = false;
            }
            $objet = ($request->request->all())['configuration'];

            // Initialisation du traitement
            $repository->initialiserTraitement(em: $em, form: $form, repository: $repository);

            // Appel et récupération des données de la méthode du traitement
            $retour['reponse'] = ($repository->getTraitement())->actionAjouter($estValide, $objet);
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }

    public function index_inscription(
        Request $request,
        ServiceEntityRepository $repository,
        EntityManagerInterface $em,
        string $typeForm,
        mixed ...$donnees,
    ): array {
        $objet = $repository->new();
        $form = $this->formFactory->create(type: $typeForm, data: $objet, options: [
            'attr' => ['id' => $donnees['donnees'][0]], //$donnees['donnees'][0] = idForm = form_type
        ]);

        $retour['form'] = $form;

        if ($request->isMethod(method: 'POST')) {

            $data = $request->request->all();
            $id_anneeacademique = $data['inscription']['anneeacademique'] ?? null;
            $id_classe = $data['inscription']['classe'] ?? null;
            $data = [];

            if (($id_anneeacademique and !$id_classe) or ($id_anneeacademique and empty(($request->request->all())['choix']))) {

                // L'utilisateur a cliqué sur le bouton inscription sans cocher la case des élèves
                if ($id_anneeacademique and $id_classe and empty(($request->request->all())['choix'])) {
                    $retour['reponse'] = new JsonResponse(data: [
                        'code' => 'ECHEC',
                        'data' => 'Veuillez cocher la case des élèves pour effectuer une inscription.',
                    ]);
                    return $retour;
                }

                // On récupère les objets
                $liste_eleves = $repository->getListeEleves();
                $liste_eleves_inscrits = $repository->findAllInscriptionByAnneeAcademique(id_anneeAcademique: (int)$id_anneeacademique);

                $trouver = false;
                $cpt = 0;

                // On récupère la liste des élèves non inscrits
                foreach ($liste_eleves as $e) {
                    $trouver = false;

                    foreach ($liste_eleves_inscrits as $i) {
                        if ($e->getId() == $i->getEleve()->getId()) {
                            $trouver = true;
                        }
                    }

                    if (!$trouver) {
                        $data[$cpt]['id'] = $e->getId();
                        $data[$cpt]['matricule'] = $e->getMatricule();
                        $data[$cpt]['nomComplet'] = $e->getNomComplet();
                        $cpt++;
                    }
                }

                $code = 'SUCCES';
            } else if ($id_anneeacademique and $id_classe and !empty(($request->request->all())['choix'])) {

                // On récupère la liste des id des élèves cochés
                $liste_id_inscrit = ($request->request->all())['choix'];
                $code = 'ECHEC';
                $cpt = 0;
                $data = 'élève(s) déjà inscrit(s) pour cette année académique dans cette classe.';


                // On récupère les objets
                $classe = $repository->getClasseById(id_classe: $id_classe);
                $anneeacademique = $repository->getAnneeAcademiqueById(id_anneeacademique: $id_anneeacademique);

                // On inscrit chaque élève
                foreach ($liste_id_inscrit as $id_eleve) {
                    $eleve = $repository->getEleveById(id_eleve: $id_eleve);
                    if ($eleve) {
                        // Si l'élève n'est pas encore inscrit on l'inscrit
                        if (!$repository->inscrire_existe(id_eleve: $id_eleve, id_anneeacademique: $id_anneeacademique, id_classe: $id_classe)) {
                            $objet_inscription = $repository->new();
                            $objet_inscription->setClasse($classe);
                            $objet_inscription->setEleve($eleve);
                            $objet_inscription->setAnneeAcademique($anneeacademique);

                            $em->persist(object: $objet_inscription);
                            $em->flush();

                            $cpt++;

                            $code = 'SUCCES';
                            $data = $cpt;
                        }
                    }
                }
            } else {
                $code = 'ECHEC';
                $data = "Veuillez renseigner les champs.";
            }

            $retour['reponse'] = new JsonResponse(data: [
                'code' => $code,
                'data' => $data,
            ]);
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }

    public function ajouter_questionnaire_evaluation(
        Request $request,
        ServiceEntityRepository $repository,
        EntityManagerInterface $em,
        string $typeForm,
        mixed ...$donnees,
    ): array {
        $objet = $repository->new();
        $form = $this->formFactory->create(type: $typeForm, data: $objet, options: [
            'attr' => ['id' => $donnees['donnees'][0]],
        ]);

        $form->handleRequest(request: $request);
        $retour['form'] = $form;

        if ($form->isSubmitted()) {
            $objet = null;
            $estValide = false;

            $data = $request->request->all()['question_evaluation'];
            $evaluation = $data['evaluation'] ?? null;
            $questionnaire = $data['questionnaire'] ?? null;
            $ordre = $data['ordre'] ?? null;

            if ($evaluation and $questionnaire and $ordre) {
                $objet['evaluation'] = $evaluation;
                $objet['questionnaire'] = $questionnaire;
                $objet['ordre'] = $ordre;
                $estValide = true;
            }

            // Initialisation du traitement
            $repository->initialiserTraitement(em: $em, form: $form, repository: $repository);

            // Appel et récupération des données de la méthode du traitement
            $retour['reponse'] = ($repository->getTraitement())->actionAjouter($estValide, $objet);
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }

    public function ajouter_correction_evaluation(
        Request $request,
        ServiceEntityRepository $repository,
        string $typeForm,
        mixed ...$donnees,
    ): array {
        $form = $this->formFactory->create(type: $typeForm, options: [
            'attr' => ['id' => $donnees['donnees'][0]],
        ]);

        $retour['form'] = $form;

        if ($request->isMethod(method: 'POST')) {
            $id = $request->request->get(key: 'id');

            // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
            $repository->initialiserTraitement(repository: $repository);

            // On récupère la liste des objets
            $liste = $repository->findEvaluationEleve(id: $id);

            function chaine_data(array $donnees, ServiceEntityRepository $repo): string
            {
                $tab = "";
                $i = 0;
                $separateur = ';x;';
                $nb = count(value: $donnees);

                foreach ($donnees as $data) {
                    $matricule = htmlspecialchars(string: $data['matricule']);
                    $nomComplet = htmlspecialchars(string: $data['nomComplet']);
                    $note = $repo->findSommeNoteByEvaluationInscription($data['id_evaluation'], $data['id_inscription']);

                    $i++;
                    $v = ($i != $nb) ? '!x!' : '';
                    $tab .=  $i . $separateur .
                        $matricule . $separateur .
                        $nomComplet . $separateur .
                        $note . $separateur .
                        lien_a(id_evaluation: $data['id_evaluation'], id_inscription: $data['id_inscription']) . $v;
                }

                return $tab;
            }

            function lien_a(int $id_evaluation, int $id_inscription): string
            {
                $crftoken = "{{ csrf_token('_auth$id_evaluation') }}";
                return <<<HTML
            <div class="d-sm-inline-flex">
                <form method="POST" action="/question-evaluation/corriger/$id_evaluation/$id_inscription" target="_blank">
                    <button class="btn btn-success btn-block" title="Corriger"><i class="typcn typcn-edit"></i></button>
                    <input type="hidden" name="_csrf_token" data-controller="csrf-protection" value="$crftoken">
                </form>
            </div>
HTML;
            }

            if ($liste) {
                $retour['reponse'] = new JsonResponse(data: [
                    'code' => 'SUCCES',
                    'html' => chaine_data(donnees: $liste, repo: $repository)
                ]);
            } else {
                $retour['reponse'] =  new JsonResponse(data: [
                    'code' => 'ECHEC',
                ]);
            }
        } else {
            $retour['reponse'] = null;
        }

        return $retour;
    }

    /**
     * liste est une méthode qui permet la gestion de l'affichage de la liste des objets
     * @param \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository $objetRepo
     * @return \Symfony\Component\HttpFoundation\Response une liste d'objet ou une liste vide
     */
    public function liste(mixed ...$donnees): Response //ServiceEntityRepository $objetRepo, ?array $critaire = null, string $propriete
    {
        $critaire = $donnees[1] ?? [];
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0], em: $donnees[3] ?? null); // $donnees[0] = $objetRepo

        // On récupère la liste des objets
        $liste = ($donnees[0])->findBy(criteria: $critaire, orderBy: ['id' => "DESC"]);
        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        $propriete = $donnees[2] ?? null; // $donnees[2] est la propriété de l'objet
        //return new JsonResponse(data: ['code' => 'SUCCES', 'data' => 'ok']);
        return (($donnees[0])->getTraitement())->actionLister($liste, $propriete);
    }

    public function liste_cours_classe(mixed ...$donnees): Response //ServiceEntityRepository $objetRepo, ?array $critaire = null, string $propriete
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0]); // $donnees[0] = $objetRepo

        // On récupère la liste des objets
        $liste = ($donnees[0])->findAllByUser(id_user: $donnees[1]);

        return (($donnees[0])->getTraitement())->actionLister($liste);
    }

    public function liste_courante(mixed ...$donnees): Response
    {
        // La variable
        $date_du_jour = new DateTime;

        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0]); // $donnees[0] = $objetRepo

        // On récupère l'id de l'inscrit
        $inscription = ($donnees[0])->findInscription($donnees[1]);
        $id_classe = $inscription->getClasse()->getId();

        // On récupère la liste des objets
        $liste = ($donnees[0])->findBy(criteria: ['classe' => $id_classe, 'anneeacademique' => $donnees[2]], orderBy: ['date' => "DESC"]);

        // On récupère que les évaluations dans lesquelles l'élève n'a pas encore composé
        $liste_evaluation = [];
        foreach ($liste as $evaluation) {
            if (!($donnees[0])->findListeReponsesByEvaluationByInscription(id_evaluation: $evaluation->getId(), id_inscription: $inscription->getId())) {

                // On récupère les évaluations en cours
                if (date_diff(baseObject: $date_du_jour, targetObject: $evaluation->getDateFin())->invert == 0) {
                    $liste_evaluation[] = $evaluation;
                }
            }
        }

        function chaine_data(array $donnees): string
        {
            $tab = "";
            $i = 0;
            $separateur = ';x;';
            $nb = count(value: $donnees);

            foreach ($donnees as $data) {
                $evaluation = htmlspecialchars(string: $data->getDescription());
                $discipline = ucfirst(string: htmlspecialchars(string: $data->getDisciplinefiliere()->getDiscipline()->getNom()));
                $type = ucfirst(string: htmlspecialchars(string: $data->getTypeevaluation()->getType()));

                $dateDebut = $data->getDateAffichage();
                $dateFin = $data->getDateFinAffichage();
                $dateEnCours = (new DateTime())->format(format: 'd/m/Y');

                $lien = "";

                if ($dateEnCours >= $dateDebut and $dateEnCours <= $dateFin) {
                    $lien = lien_a(id: $data->getId());
                } else {
                    $lien = lien_a_inactif();
                }

                $i++;
                $v = ($i != $nb) ? '!x!' : '';
                $tab .=  $i . $separateur .
                    $evaluation . $separateur .
                    $type . $separateur .
                    $discipline . $separateur .
                    $lien . $v;
            }

            return $tab;
        }

        function lien_a(int $id): string
        {
            $crftoken = "{{ csrf_token('_auth$id') }}";
            return <<<HTML
        <div class="d-sm-inline-flex">
            <form method="POST" action="/question-evaluation/composition/$id">
                <button class="btn btn-success btn-block" title="Composer"><i class="typcn typcn-edit"></i></button>
                <input type="hidden" name="_csrf_token" data-controller="csrf-protection" value="$crftoken">
            </form>
        </div>
HTML;
        }

        function lien_a_inactif(): string
        {
            return <<<HTML
            <div class="d-sm-inline-flex">
                <button class="btn btn-danger btn-block" title="Composer"><i class="typcn typcn-edit"></i></button>
            </div>
HTML;
        }

        if ($liste_evaluation) {
            return new JsonResponse(data: [
                'code' => 'SUCCES',
                'html' => chaine_data(donnees: $liste_evaluation)
            ]);
        } else {
            return new JsonResponse(data: [
                'code' => 'ECHEC',
            ]);
        }
    }

    public function liste_disponible(mixed ...$donnees): Response
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0]); // $donnees[0] = $objetRepo

        // On récupère l'id de l'inscrit
        $id_classe = (($donnees[0])->findInscription($donnees[1]))->getClasse()->getId();

        // On récupère la liste des objets
        $liste = ($donnees[0])->findBy(criteria: ['classe' => $id_classe], orderBy: ['id' => "DESC"]);

        function chaine_data_2(array $donnees): string
        {
            $tab = "";
            $i = 0;
            $separateur = ';x;';
            $nb = count(value: $donnees);

            foreach ($donnees as $data) {
                $discipline = ucfirst(string: htmlspecialchars(string: $data->getDisciplineFiliere()->getDiscipline()->getNom()));
                $professeur = htmlspecialchars(string: $data->getCours()->getProfesseur()->getNomComplet());
                $titre = htmlspecialchars(string: $data->getCours()->getTitre());
                $fichier = Utilitaire::telecharger_fichier(path: $data->getCours()->getId());

                $i++;
                $v = ($i != $nb) ? '!x!' : '';
                $tab .=  $i . $separateur .
                    $discipline . $separateur .
                    $professeur . $separateur .
                    $titre . $separateur .
                    $fichier .  $v;
            }

            return $tab;
        }

        if ($liste) {
            return new JsonResponse(data: [
                'code' => 'SUCCES',
                'html' => chaine_data_2(donnees: $liste)
            ]);
        } else {
            return new JsonResponse(data: [
                'code' => 'ECHEC',
            ]);
        }
    }

    public function liste_resultat(mixed ...$donnees): Response
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        ($donnees[0])->initialiserTraitement(repository: $donnees[0]); // $donnees[0] = $objetRepo

        // On récupère l'id de l'inscrit
        $inscription = ($donnees[0])->findInscription($donnees[1]);
        $id_classe = $inscription->getClasse()->getId();

        // On récupère la liste des objets
        $liste = ($donnees[0])->findBy(criteria: ['classe' => $id_classe, 'anneeacademique' => $donnees[2]], orderBy: ['date' => "DESC"]);

        // On récupère que les évaluations dans lesquelles l'élève n'a pas encore composé
        $liste_questionnaires = [];
        $liste_correction = [];
        $liste_reponses = [];
        $liste_evaluations = [];
        $note = 0;
        $somme_note = 0;
        $nb_reponse = 0;
        $id_type_questionnaire = $donnees[4];
        $id_type_questionnaire_fichier = $donnees[5];
        $reponse = [];
        $cpt = 0;
        $date_du_jour = (new DateTime)->format(format: 'd/m/Y');

        foreach ($liste as $evaluation) {
            $somme_note = 0;

            $reponse = ($donnees[0])->findListeReponsesByEvaluationByInscription(id_evaluation: $evaluation->getId(), id_inscription: $inscription->getId());
            if ($reponse) {
                // On vérifie si la période d'affichage est arrivée
                if($date_du_jour <= $evaluation->getDateFinAffichage())
                {
                    continue;
                }

                $liste_questionnaires = ($donnees[0])->getQuestionnaires(id_evaluation: $evaluation->getId());

                $nb_questionnaire = count(value: $liste_questionnaires);
                $note = $evaluation->getBareme() / $nb_questionnaire;
                $note = round(num: $note, precision: 2);

                foreach ($liste_questionnaires as $questionnaire) {
                    $liste_reponses = ($donnees[0])->findListeReponsesByQuestionnaireByInscription(id_evaluation: $evaluation->getId(), id_questionnaire: $questionnaire['id_questionnaire'], id_inscription: $inscription->getId());

                    if ($questionnaire['id_type_questionnaire'] != $id_type_questionnaire) {
                        $somme_note += ($liste_reponses[0])->getNote();
                        continue; // On passe au questionnaire suivant
                    }

                    $liste_correction = ($donnees[0])->findListeCorrectionsByQuestionnaire($questionnaire['id_questionnaire']);
                    $nb_reponse = count(value: $liste_reponses);

                    if ($nb_reponse > 1) {
                        $note /= $nb_reponse;
                        $note = round(num: $note, precision: 2);
                    }

                    foreach ($liste_reponses as $reponse) {
                        foreach ($liste_correction as $correction) {
                            if ($reponse->getProposition()->getId() == $correction->getProposition()->getId()) {
                                // On modifie la réponse pour ajouter la note
                                $reponse->setNote($note);
                                ($donnees[3])->flush();

                                $somme_note += $note;
                            }
                        }
                    }
                }

                //On ajoute les données dans le tableau
                $liste_evaluations[$cpt]['evaluation'] = $evaluation;
                $liste_evaluations[$cpt]['note'] = $somme_note;
                $cpt++;
            }
        }

        function chaine_data(array $donnees, $repository, int $id_type_q_f): string
        {
            $tab = "";
            $i = 0;
            $separateur = ';x;';
            $nb = count(value: $donnees);

            foreach ($donnees as $data) {
                $evaluation = htmlspecialchars(string: ($data['evaluation'])->getDescription());
                $discipline = ucfirst(string: htmlspecialchars(string: ($data['evaluation'])->getDisciplinefiliere()->getDiscipline()->getNom()));
                $type = ucfirst(string: htmlspecialchars(string: ($data['evaluation'])->getTypeevaluation()->getType()));
                $note = $data['note'];

                $lien = id_type_questionnaire_fichier(repository: $repository, id_evaluation: ($data['evaluation'])->getId(), tqf: $id_type_q_f) ? '' : lien_a(id: ($data['evaluation'])->getId());

                $i++;
                $v = ($i != $nb) ? '!x!' : '';
                $tab .=  $i . $separateur .
                    $evaluation . $separateur .
                    $type . $separateur .
                    $discipline . $separateur .
                    $note . $separateur .
                    $lien . $v;
            }

            return $tab;
        }

        function lien_a(int $id): string
        {
            $crftoken = "{{ csrf_token('_auth$id') }}";
            return <<<HTML
        <div class="d-sm-inline-flex">
            <form method="POST" action="/question-evaluation/copie/$id" class="mr-3" target="_blank">
                <button class="btn btn-success btn-block" title="Copie"><i class="typcn typcn-pen"></i></button>
                <input type="hidden" name="_csrf_token" data-controller="csrf-protection" value="$crftoken">
            </form>
            <form method="POST" action="/question-evaluation/correction/$id" target="_blank">
                <button class="btn btn-danger btn-block" title="Correction"><i class="typcn typcn-upload"></i></button>
                <input type="hidden" name="_csrf_token" data-controller="csrf-protection" value="$crftoken">
            </form>
        </div>
HTML;
        }

        function id_type_questionnaire_fichier($repository, int $id_evaluation, int $tqf): bool
        {
            $retour = false;
            $liste_q = $repository->getQuestionnaires(id_evaluation: $id_evaluation);
            foreach ($liste_q as $q) {
                if ($q['id_type_questionnaire'] == $tqf) {
                    $retour = true;
                    break;
                }
            }
            return $retour;
        }

        if ($liste) {
            return new JsonResponse(data: [
                'code' => 'SUCCES',
                'html' => chaine_data(donnees: $liste_evaluations, repository: $donnees[0], id_type_q_f: $id_type_questionnaire_fichier)
            ]);
        } else {
            return new JsonResponse(data: [
                'code' => 'ECHEC',
            ]);
        }
    }

    /**
     * check est une méthode qui permet la gestion du chargement des données pour la modification du formulaire
     * @param \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository $objetRepo
     * @param mixed $id
     * @return \Symfony\Component\HttpFoundation\Response Un objet contenu les champs du formulaire
     */
    public function check(ServiceEntityRepository $objetRepo, mixed $id, ?string $propriete = null): Response
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        $objetRepo->initialiserTraitement(repository: $objetRepo);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($objetRepo->getTraitement())->actionCheck($id, $propriete);
    }

    /**
     * modifier est une méthode qui permet la modification du formulaire
     * @param \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository $objetRepo
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @param string $formType
     * @param string $nomFormulaire
     * @return \Symfony\Component\HttpFoundation\Response Le code SUCCES ou ECHEC
     */
    public function modifier(
        ServiceEntityRepository $objetRepo,
        EntityManagerInterface $em,
        Request $request,
        int $id,
        string $formType,
        string $nomFormulaire = "form_type",
        mixed $mixe = null,
        ?string $propriete = null,
        ?UserPasswordHasherInterface $userPasswordHasher = null
    ): Response {
        $objet = $objetRepo->findOneBy(criteria: ['id' => $id]);
        $form = $this->formFactory->create(type: $formType, data: $objet, options: [
            'attr' => ['id' => $nomFormulaire]
        ]);

        $form->handleRequest(request: $request);
        $is_select = false;
        //Gestion du cas du select lorsque la variable $mixe existe ----------------------  
        if ($mixe) {
            $erreur = Utilitaire::getErreur(form: $form);
            if (count(value: $erreur) <= 1 && $erreur[$mixe['libelle']] && $mixe['objet']) {
                $propriete = 'set' . $propriete;
                $objet->$propriete($mixe['objet']);
                $is_select = true;
            } else {
                // Gestion de 4 select
                $cpt = 0;
                foreach ($mixe as $element) {
                    if ($element) {
                        foreach ($erreur as $key => $value) {
                            if ($element['libelle'] == $key) {
                                $cpt++;
                            }
                        }
                    }
                }

                if ($cpt == 4) {
                    $is_select = true;
                    $objet->setDisciplinefiliere($mixe[0]['objet']);
                    $objet->setPeriodeacademique($mixe[1]['objet']);
                    $objet->setClasse($mixe[2]['objet']);
                    //$objet['filiere'] = $mixe[3]['objet'];
                }
            }
        }
        //--------------------------------------------------------------------------------
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        $objetRepo->initialiserTraitement(form: $form, em: $em);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($objetRepo->getTraitement())->actionModifier($objet, $is_select, $userPasswordHasher);
    }

    public function modifier_questionnaire(
        ServiceEntityRepository $objetRepo,
        EntityManagerInterface $em,
        Request $request,
        int $id,
        string $formType,
        string $nomFormulaire = "form_type",
        mixed $mixe = null,
    ): Response {
        $objet = $objetRepo->findOneBy(criteria: ['id' => $id]);
        $form = $this->formFactory->create(type: $formType, data: $objet, options: [
            'attr' => ['id' => $nomFormulaire]
        ]);

        $form->handleRequest(request: $request);
        $is_select = false;
        $data = null;

        //Gestion du cas du select lorsque la variable $mixe existe ----------------------  
        $erreur = Utilitaire::getErreur(form: $form);
        if (count(value: $erreur) <= 1 && $erreur[$mixe['libelle']] && $mixe['objet']) {
            $objet->setFiliere($mixe['objet']);
            $data['objet'] = $objet;
            $data['fichiers'] = null;
            $is_select = true;

            // On conserve les fichiers au cas ou ils existent
            $fichiers = $request->files->all();
            if ($fichiers['fichier'] ?? false) {
                $data['fichiers'] = $fichiers;
            }
        }
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        $objetRepo->initialiserTraitement(form: $form, em: $em, repository: $objetRepo);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($objetRepo->getTraitement())->actionModifier($is_select, $data);
    }

    public function modifier_cours_classe(
        ServiceEntityRepository $objetRepo,
        EntityManagerInterface $em,
        Request $request,
        int $id,
    ): Response {
        $objet = $objetRepo->findOneBy(criteria: ['id' => $id]);

        $data = $request->request->all();
        $cours = $data['cours_classe']['cours'] ?? null;
        $classe = $data['cours_classe']['classe'] ?? null;
        $discipline = $data['cours_classe']['discipline'] ?? null;
        $estValide = false;

        if ($cours and $classe and $discipline) {
            // On récupère les objets
            $classe = $em->getRepository(className: Classe::class)->findOneBy(criteria: ['id' => (int)$classe]);
            $cours = $em->getRepository(className: Cours::class)->findOneBy(criteria: ['id' => (int)$cours]);
            $disciplineFiliere = $em->getRepository(className: DisciplineFiliere::class)->findOneBy(criteria: ['id' => (int)$discipline]);

            $objet->setCours($cours);
            $objet->setClasse($classe);
            $objet->setDisciplineFiliere($disciplineFiliere);
            $estValide = true;
        }

        //--------------------------------------------------------------------------------
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        $objetRepo->initialiserTraitement(em: $em);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($objetRepo->getTraitement())->actionModifier($estValide);
    }

    public function modifier_fichier(
        Request $request,
        ServiceEntityRepository $repository,
        EntityManagerInterface $em,
        string $typeForm,
        mixed ...$donnees,
    ): Response {
        $objet = $repository->findOneBy(criteria: ['id' => $donnees['donnees'][0]]);
        $form = $this->formFactory->create(type: $typeForm, data: $objet, options: [
            'attr' => ['id' => $donnees['donnees'][1]],
        ]);

        $form->handleRequest(request: $request);

        $estValide = null;
        // Gestion des cas avec fichier
        $donnees['donnees'][1] = $objet;
        if ($form->get(name: 'titre')->getData()) {
            $estValide = true;
        }

        // Initialisation du traitement
        $repository->initialiserTraitement(em: $em, form: $form, repository: $repository);

        // Appel et récupération des données de la méthode du traitement
        return ($repository->getTraitement())->actionModifier($estValide, $donnees);
    }

    public function modifier_questionnaire_evaluation(
        ServiceEntityRepository $objetRepo,
        EntityManagerInterface $em,
        Request $request,
        int $id,
        string $formType,
        string $nomFormulaire = "form_type",
        mixed $mixe = null,
        ?string $propriete = null,
        ?UserPasswordHasherInterface $userPasswordHasher = null
    ): Response {
        $objet = $objetRepo->findOneBy(criteria: ['id' => $id]);
        $form = $this->formFactory->create(type: $formType, data: $objet, options: [
            'attr' => ['id' => $nomFormulaire]
        ]);

        $form->handleRequest(request: $request);
        $estValide = false;
        $donnees = null;

        $data = $request->request->all()['question_evaluation'];
        $evaluation = $data['evaluation'] ?? null;
        $questionnaire = $data['questionnaire'] ?? null;
        $ordre = $data['ordre'] ?? null;

        if ($evaluation and $questionnaire and $ordre) {
            $donnees['evaluation'] = $evaluation;
            $donnees['questionnaire'] = $questionnaire;
            $donnees['ordre'] = $ordre;
            $estValide = true;
        }

        //--------------------------------------------------------------------------------
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        $objetRepo->initialiserTraitement(form: $form, em: $em, repository: $objetRepo);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($objetRepo->getTraitement())->actionModifier($estValide, $objet, $donnees);
    }

    /**
     * supprimer est une méthode qui permet la suppression d'un objet
     * @param \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository $objetRepo
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function supprimer(ServiceEntityRepository $objetRepo, EntityManagerInterface $em, int $id): Response
    {
        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        $objetRepo->initialiserTraitement(em: $em, repository: $objetRepo);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action
        return ($objetRepo->getTraitement())->actionSupprimer($id);
    }

    /**
     * select est une méthode qui permet la gestion de la sélection d'une liste déroulante d'un objet
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository $objetRepo
     * @return Response est un tableau qui contient le contenu json ou null selon le cas et un formulaire.
     */
    public function select(Request $request, ServiceEntityRepository $objetRepo): Response
    {
        // On récupère les paramètres du formulaire envoyé depuis le js
        $id = $request->request->get(key: 'id');
        $id = $id ?: 0;
        $label = $request->request->get(key: 'label');

        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        $objetRepo->initialiserTraitement(repository: $objetRepo);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action

        return $objetRepo->actionSelect(label: $label, mixe: $id);
    }

    public function select_evaluation(Request $request, ServiceEntityRepository $objetRepo): Response
    {
        // On récupère les paramètres du formulaire envoyé depuis le js
        $id_typeevaluation = $request->request->get(key: 'id_typeevaluation');
        $id_discipline_filiere = $request->request->get(key: 'id_discipline_filiere');
        $id_periode_academique = $request->request->get(key: 'id_periode_academique');
        $id_classe = $request->request->get(key: 'id_classe');

        $id_typeevaluation = $id_typeevaluation ?: 0;
        $id_discipline_filiere = $id_discipline_filiere ?: 0;
        $id_periode_academique = $id_periode_academique ?: 0;
        $id_classe = $id_classe ?: 0;

        $label = $request->request->get(key: 'label');

        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        $objetRepo->initialiserTraitement(repository: $objetRepo);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action

        return $objetRepo->actionSelect(label: $label, mixe: [
            $id_typeevaluation,
            $id_discipline_filiere,
            $id_periode_academique,
            $id_classe,
        ]);
    }

    public function select_correction(Request $request, ServiceEntityRepository $objetRepo): Response
    {
        // On récupère les paramètres du formulaire envoyé depuis le js
        $id = $request->request->get(key: 'id');
        $id = $id ?: 0;
        $label = $request->request->get(key: 'label');

        // On instancie un objet qui hérite de TraitementInterface pour gérer le traitement
        $objetRepo->initialiserTraitement(repository: $objetRepo);

        // On appelle la méthode getTraitement qui nous retourne un objet de type traitementInterface
        // Ensuite on appelle la méthode appropriée pour traiter l'action

        if ($label == 'Période') {
            $classe = $objetRepo->getClasse($id);
            $id = $classe->getFiliere()->getFiliere()->getCycle()->getId();
            return $objetRepo->actionSelect(label: $label, mixe: $id);
        } else {
            $id_classe = $request->request->get(key: 'id_classe');
            return $objetRepo->actionSelect(label: $label, mixe: [$id, $id_classe]);
        }
    }
}
