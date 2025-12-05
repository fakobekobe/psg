// Les variables locales
let P_URL = 'question-evaluation', 
NOM_FORM = "question_evaluation_", 
texteTitre = "Ajouter un épreuve";
const URL_SELECT_FILIERE = "discipline-filiere",
    PLACEHOLDER_DISCIPLINE = "Discipline",
    URL_SELECT_CYCLE = "periode-academique",
    URL_SELECT_CLASSE = "classe",
    URL_SELECT_FI = "filiere-annee-courante",
    PLACEHOLDER_PERIODE = "Période",
    PLACEHOLDER_CLASSE = "Classe",
    PLACEHOLDER_FILIERE = "Filière",
    URL_SELECT_EVALUATION = "evaluation",
    PLACEHOLDER_EVALUATION = "Evaluation",
    URL_SELECT_QUESTIONNAIRE = "questionnaire",
    PLACEHOLDER_QUESTIONNAIRE = "Question",
    LABEL_SELECT = ['cycle', 'typeevaluation', 'ordre'];

// Edition des fonction ---------------------
function action_ajouter(
    PREFIX_CHAMP,
    PREFIX_URL,
    NOM_FORMULAIRE = 'form_type',
    NOM_TABLEAU = 'dataTable',
    NOM_BTN_AJOUTER = 'ajouter',
    FONCTION_ECHEC = null
) {
    // Les variables globales
    const NOM_BTN_ANNULER = 'annulerajouter';
    const URL_LISTE = PREFIX_URL;
    let PREFIX_URL_U = '/' + PREFIX_URL + '/modifier/';
    let loader = $('#bloc-loader');

    let btn = $('#' + NOM_BTN_AJOUTER);

    btn.on('click', function (e) {
        e.preventDefault();
        // Affichage du chargement
        imageChargement(loader, 'flex');

        let form = $('#' + NOM_FORMULAIRE);
        let URL = form[0].action;

        let data = new FormData(form[0]);
        if (id_modifier) {
            URL = PREFIX_URL_U + id_modifier;
        }

        fetch(URL, {
            method: 'POST',
            body: data,
        })
            .then(reponse => reponse.json())
            .then(json => traitementJson(json));
    });

    // Fonction de gestion du traitement du retour des données json du fetch
    function traitementJson(data) {
        // Annulation du chargement
        imageChargement(loader, 'none');

        // On supprime toutes les erreurs
        suppressionErreurs();

        switch (data.code) {
            case 'SUCCES':
                traitement_succes();
                break;

            case 'ECHEC':
                APPEL_FONCTION_ECHEC(data.erreurs);
                break;

            case 'EXCEPTION':
                traitement_exception(data.exception);
                break;
        }
    };

    const traitement_succes = function () {

        let title = 'Enregistrement !';
        let text = 'Votre enregistrement a été effectué avec succès.';

        // On modifie le titre et le texte lorsque c'est une modification
        if (id_modifier) {
            title = 'Modification !';
            text = 'Votre modification a été effectuée avec succès.';
            id_modifier = 0;
        }

        Swal.fire({
            title: title,
            text: text,
            icon: "success",
            timer: 1500
        });

        // On charge les nouvelles données avec la fonction de l'action liste
        action_liste(URL_LISTE, NOM_TABLEAU);
    };

    const traitement_echec = function (erreurs) {
        if (erreurs.length == 0) {
            Swal.fire({
                title: "Erreur",
                text: "Veuillez renseiller tous les champs.",
                icon: "error",
                timer: 3000
            });
            return;
        }

        $.each(erreurs, function (key, valeur) {
            // On récupère le champ cible
            let input = $('#' + PREFIX_CHAMP + key);
            input.addClass("is-invalid");

            $.each(valeur, function (i, message) {
                // On créé une div pour afficher le message d'erreur
                creerDiv(input, message);
            });
        });
    };

    const traitement_exception = function (exception) {
        Swal.fire({
                title: "Erreur",
                text: exception,
                icon: "error",
                timer: 5000
            });
    }

    let APPEL_FONCTION_ECHEC = FONCTION_ECHEC ? FONCTION_ECHEC[0] : traitement_echec;

    const creerDiv = function (cible, message) {
        // Créér un div pour afficher l'erreur
        let div = $('<div />');
        div.attr({
            class: "invalid-feedback d-block"
        });

        // On ajoute le message
        div.text(message);

        // Ajouter l'élément
        cible.after(div);
    };
}


// Appel des fonctions d'action-------------

// Exécution de la fonction de l'action ajouter
action_ajouter(NOM_FORM, P_URL); 

// Exécution de la fonction de l'action liste
action_liste(P_URL);

// Exécution de la fonction de l'action supprimer
action_supprimer(P_URL);

// Exécution de la fonction de l'action check
action_check(P_URL, NOM_FORM, "Modifier une épreuve", check_select, LABEL_SELECT);

// On exécute le traitement du bouton de fermeture
fermer_formulaire();

// On exécute le traitement du bouton de close
close_formulaire();

// On initialise le contenu des champs select 
initialiser_select(NOM_FORM + "filiere", NOM_FORM + "discipline", URL_SELECT_FILIERE, PLACEHOLDER_DISCIPLINE);
initialiser_select(NOM_FORM + "filiere", NOM_FORM + "classe", URL_SELECT_CLASSE, PLACEHOLDER_CLASSE);
initialiser_select(NOM_FORM + "cycle", NOM_FORM + "periode", URL_SELECT_CYCLE, PLACEHOLDER_PERIODE);
initialiser_select(NOM_FORM + "cycle", NOM_FORM + "filiere", URL_SELECT_FI, PLACEHOLDER_FILIERE);
initialiser_select(NOM_FORM + "discipline", NOM_FORM + "questionnaire", URL_SELECT_QUESTIONNAIRE, PLACEHOLDER_QUESTIONNAIRE);

// Rédéfinition de l'initialisation du champ select
function initialiser_select_evaluation(
    select_principal, 
    select_cible, 
    URL, 
    LABEL, 
    ID_DISCIPLINE_FILIERE,
    ID_PERIODE_ACADEMIQUE,
    ID_CLASSE
) {
    $('#' + select_principal).on('change', function (e) {
        const PATH_URL = "/" + URL + "/select",
                ID_DF = $('#' + ID_DISCIPLINE_FILIERE),
                ID_PA = $('#' + ID_PERIODE_ACADEMIQUE),
                ID_C = $('#' + ID_CLASSE);

        let form = new FormData();
        form.append('id_typeevaluation', $(this).val());
        form.append('id_discipline_filiere', ID_DF.val());
        form.append('id_periode_academique', ID_PA.val());
        form.append('id_classe', ID_C.val());
        form.append('label', LABEL);

        fetch(PATH_URL, {
            method: 'POST',
            body: form
        })
            .then(reponse => reponse.json())
            .then(json => traitementJson(json))
            ;

        // Fonction de gestion du traitement du retour des données jsaon du fetch
        function traitementJson(data) {

            switch (data.code) {
                case 'SUCCES':
                    traitement_succes(data.html);
                    break;

                case 'ECHEC':
                    traitement_echec(data.erreur);
                    break;
            }
        };

        const traitement_succes = function (html) {
            $('#' + select_cible).html(html);
        };

        const traitement_echec = function (erreur) {
            $('#' + select_cible).html(erreur);
        };


    });
}

// Exécution de la fonction
initialiser_select_evaluation(
    NOM_FORM + "typeevaluation", 
    NOM_FORM + "evaluation", 
    URL_SELECT_EVALUATION, 
    PLACEHOLDER_EVALUATION, 
    NOM_FORM + "discipline",
    NOM_FORM + "periode",
    NOM_FORM + "classe"
);