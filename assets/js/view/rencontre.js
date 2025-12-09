// Les variables locales
let P_URL = 'rencontre', 
NOM_FORM = P_URL + "_", 
texteTitre = 'Ajouter une rencontre';
const URL_SELECT = "calendrier";
const PLACEHOLDER = "Calendrier",
    LABEL_SELECT = ['championnat'];


// Rédéfinition de la méthode Action ajouter
function action_ajouter(
    PREFIX_CHAMP,
    PREFIX_URL,
    NOM_FORMULAIRE = 'form_type',
    NOM_TABLEAU = 'dataTable',
    NOM_BTN_AJOUTER = 'ajouter'
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

            btn.html(texteBtn);
            id_modifier = 0;

            form[0].reset();
            $('#' + NOM_BTN_ANNULER).trigger('click');
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
action_check(P_URL, NOM_FORM, "Modifier une rencontre", check_select, LABEL_SELECT); // A modifier *******

// On exécute le traitement du bouton de fermeture
fermer_formulaire();

// On exécute le traitement du bouton de close
close_formulaire();

// On initialise le contenu du champ select
initialiser_select("rencontre_championnat", "rencontre_calendrier", URL_SELECT, PLACEHOLDER);

