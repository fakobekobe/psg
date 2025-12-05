// Les variables locales
const P_URL = 'questionnaire',
    NOM_FORM = P_URL + '_',
    P_FORMULAIRE = P_URL + '_type',
    P_TABLEAU = 'dataTableQuestionnaire',
    URL_SELECT_FI = "discipline-filiere-professeur",
    PLACEHOLDER_FILIERE = "Filière",
    LABEL_SELECT = ['professeur', 'typequestionnaire'];
let texteTitre = 'Ajouter une question'; 

// Les variables locales de proposition
const PRO_URL = 'proposition',
    NOM_FORM_PRO = PRO_URL + '_',
    PRO_FORMULAIRE = PRO_URL + '_type',
    PRO_BTN = 'ajouter_proposition',
    PRO_TABLEAU = 'dataTableProposition';

// Les variables locales de correction
const PCO_URL = 'correction',
    PCO_TABLEAU = 'dataTableCorrection';

// Redéfinition de méthode ------------------------------
function action_supprimer_q(URL, NOM_TABLEAU, execution_action_liste) {
    // Les variables globales
    let table = $('#' + NOM_TABLEAU);
    const URL_LISTE = URL;

    table.on('click', '.deleteBtn', function (e) {
        e.preventDefault();
        Swal.fire({
            title: "Voulez-vous supprimer cette ligne ?",
            text: this.dataset.nom,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Oui, j\'en suis sûre !",
            cancelButtonText: "Annuler",
        }).then((result) => {
            if (result.isConfirmed) {

                let URL_FETCH = "/" + URL + "/supprimer/" + this.dataset.id;
                let data = new FormData();
                data.append('id', this.dataset.id);

                fetch(URL_FETCH, {
                    method: 'POST',
                    body: data
                })
                    .then(reponse => reponse.json())
                    .then(json => traitementJson(json));
            }
        });
    });

    const traitementJson = function (data) {
        switch (data.code) {
            case 'SUCCES':
                traitement_succes(data.message);
                break;

            case 'ECHEC':
                traitement_echec(data.message);
                break;
        }
    };

    const traitement_succes = function (message) {
        Swal.fire({
            title: "Supression !",
            text: message,
            icon: "success",
            timer: 1500
        });

        execution_action_liste();

    };

    const traitement_echec = function (message) {
        Swal.fire({
            title: "Supression !",
            text: message,
            icon: "danger",
            timer: 1500
        });
    };
}

const function_action_liste_questionnaire = function()
{
    action_liste(P_URL, P_TABLEAU);
    action_liste(PRO_URL, PRO_TABLEAU);
    action_liste(PCO_URL, PCO_TABLEAU);
}

function affichage_champ_fichier(NOM_URL, NOM_CHMAP_SELECT = 'typequestionnaire', NOM_HIDDEN = 'id_type_questionnaire_fichier', NOM_CHAMP_FICHIER = 'champ_fichier')
{
    let btn = $('#' + NOM_URL + NOM_CHMAP_SELECT);
    btn.on('change', function(e){
        let questionnaire_fichier = $('#' + NOM_HIDDEN),
        id_type_questionnaire_fichier = questionnaire_fichier.val();

        let champ_fichier = $('#' + NOM_CHAMP_FICHIER);
         champ_fichier.toggle($(this).val() == id_type_questionnaire_fichier);
    });
}

function afficher_fichier(
    URL,
    NOM_TABLEAU = 'dataTableQuestionnaire',
   ) {
    // Les variables globales
    let table = $('#' + NOM_TABLEAU);

    table.on('click', '.viewBtn', function (e) {
        e.preventDefault();
        let $form = new FormData();
        const id = this.dataset.id;
        $form.append('id', id);
        let URL_FETCH = "/" + URL + "/afficher/" + id;

        fetch(URL_FETCH, {
            method: 'POST',
            body: $form
        })
            .then(reponse => reponse.json())
            .then(json => traitementJson(json))
            ;

        const traitementJson = function (data) {
            switch (data.code) {
                case 'SUCCES':
                    traitement_succes(data.html);
                    break;
            }
        };

        // Définition des fonctions
        const traitement_succes = function (html) {
            let contenu = "<ol>";
            html.forEach((o, i) => {
                contenu += "<li>" + o + "</li>";
            });
            contenu += "</ol>";

            Swal.fire({
                title: "Télécharger les fichiers",
                html: contenu,
                icon: "info",
            });
        };
        
    });
}

//------------------------------------------------------


afficher_fichier(P_URL);
affichage_champ_fichier(NOM_FORM);

// Exécution de la fonction de l'action ajouter
action_ajouter(NOM_FORM, P_URL, P_FORMULAIRE, P_TABLEAU);

// Exécution de la fonction de l'action liste
action_liste(P_URL, P_TABLEAU);

// Exécution de la fonction de l'action supprimer
action_supprimer_q(P_URL, P_TABLEAU, function_action_liste_questionnaire);

// Exécution de la fonction de l'action check
action_check(P_URL, NOM_FORM, "Modifier une question", check_select, LABEL_SELECT, P_TABLEAU); 

// On exécute le traitement du bouton de fermeture
fermer_formulaire();

// On exécute le traitement du bouton de close
close_formulaire();

initialiser_select(P_URL + "_professeur", P_URL + "_filiere", URL_SELECT_FI, PLACEHOLDER_FILIERE);

cible_onglet('add', P_URL, 'proposition', P_TABLEAU);

//-------------------------------
// Gestion du volet Proposition

// Redéfinition des méthodes
function action_ajouter_proposition(
    PREFIX_CHAMP,
    PREFIX_URL,
    NOM_FORMULAIRE = 'form_type',
    NOM_TABLEAU = 'dataTable',
    NOM_BTN_AJOUTER = 'ajouter',
    SOURCE_VERIFIE = true,
) {
    // Les variables globales
    const URL_LISTE = PREFIX_URL;
    let PREFIX_URL_U = '/' + PREFIX_URL + '/modifier/',
        loader = $('#bloc-loader')
        SOURCE_ID = 0;

    let btn = $('#' + NOM_BTN_AJOUTER);

    btn.on('click', function (e) {
        e.preventDefault();
        // Affichage du chargement
        imageChargement(loader, 'flex');

        let form = $('#' + NOM_FORMULAIRE);
        let URL = form[0].action;

        let data = new FormData(form[0]);
        SOURCE_ID = SOURCE_VERIFIE ? id_source.proposition : id_source.correction;
        
        data.append('id', SOURCE_ID);

        if (id_modifier) {
            URL = PREFIX_URL_U + id_modifier;
        }else if(!SOURCE_ID)
        {
            // Annulation du chargement
            imageChargement(loader, 'none');
            traitement_exception('Veuillez sélectionner un questionnaire.');
            return 0;
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
                traitement_echec(data.erreurs);
                break;

            case 'EXCEPTION':
                traitement_exception(data.exception);
                break;
        }
    };

    const traitement_succes = function () {

        let form = $('#' + NOM_FORMULAIRE);
        let title = 'Enregistrement !';
        let text = 'Votre enregistrement a été effectué avec succès.';

        // On modifie le titre et le texte lorsque c'est une modification
        if (id_modifier) {
            title = 'Modification !';
            text = 'Votre modification a été effectuée avec succès.';
        }

        Swal.fire({
            title: title,
            text: text,
            icon: "success",
            timer: 1500
        });

        form[0].reset();
        if (id_modifier) {
            id_modifier = 0;
        }
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

const function_action_liste_proposition = function()
{
    action_liste(PRO_URL, PRO_TABLEAU);
    action_liste(PCO_URL, PCO_TABLEAU);
}

// Exécution des méthode d'actions
action_ajouter_proposition(NOM_FORM_PRO, PRO_URL, PRO_FORMULAIRE, PRO_TABLEAU, PRO_BTN);
action_liste(PRO_URL, PRO_TABLEAU);
action_supprimer_q(PRO_URL, PRO_TABLEAU, function_action_liste_proposition);
action_check(PRO_URL, NOM_FORM_PRO, "Modifier une proposition", null, null, PRO_TABLEAU); 
cible_onglet('addCorrection', PRO_URL, 'correction', PRO_TABLEAU, false);

//----------------------------------
// Gestion du volet Correction

// Redéfinition des méthodes
function action_ajouter_correction(
    PREFIX_URL,
    NOM_TABLEAU,
    TABLEAU_LISTE,
    SOURCE_VERIFIE = true,
) {
    // Les variables globales
    const URL_LISTE = PREFIX_URL,
            URL = '/' + PREFIX_URL;
    let loader = $('#bloc-loader'),
        table = $('#' + NOM_TABLEAU),
        SOURCE_ID = 0;

    table.on('click', '.addCorrectionBtn', function (e) {
        e.preventDefault();
        // Affichage du chargement
        imageChargement(loader, 'flex');

        let data = new FormData();
        SOURCE_ID = SOURCE_VERIFIE ? id_source.proposition : id_source.correction;
        
        data.append('id', SOURCE_ID);

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
                traitement_echec(data.erreur);
                break;
        }
    };

    const traitement_succes = function () {

        Swal.fire({
            title: 'Enregistrement !',
            text: 'Votre enregistrement a été effectué avec succès.',
            icon: "success",
            timer: 1500
        });

        // On charge les nouvelles données avec la fonction de l'action liste
        action_liste(URL_LISTE, TABLEAU_LISTE);

    };

    const traitement_echec = function (erreur) {
        Swal.fire({
            title: "Erreur",
            text: erreur,
            icon: "error",
            timer: 5000
        });
    };
}

action_ajouter_correction(PCO_URL, PRO_TABLEAU, PCO_TABLEAU, false);
action_liste(PCO_URL, PCO_TABLEAU);
action_supprimer(PCO_URL, PCO_TABLEAU);
