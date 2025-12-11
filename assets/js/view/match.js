// Les variables locales
let P_URL = 'match',
    NOM_FORM = "match_dispute_";
const URL_SELECT = "calendrier";
const PLACEHOLDER = "Calendrier";

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

function valider1(
    PREFIX_URL,
    NOM_FORMULAIRE = 'form_type',
    btn_valider = 'valider_1',
    id_saison = "match_dispute_saison",
    id_calendrier = "match_dispute_calendrier",
    id_contenu_rencontre = 'contenu_rencontre'
) {

    let btn = $('#' + btn_valider),
        loader = $('#bloc-loader'),
        contenu_rencontre = $('#' + id_contenu_rencontre);

    btn.on('click', function (e) {
        e.preventDefault();
        let btn_saison = $('#' + id_saison),
        btn_calendrier = $('#' + id_calendrier);

        if (!btn_saison.val()) {
            Swal.fire({
                title: 'Erreur',
                text: 'Veuillez renseigner le champ saison.',
                icon: "error",
                timer: 3000
            });
            return;
        }

        if (!btn_calendrier.val()) {
            Swal.fire({
                title: 'Erreur',
                text: 'Veuillez renseigner le champ calendrier.',
                icon: "error",
                timer: 3000
            });
            return;
        }

        // On va cherche les données de la rencontre et les équipes
        
        let form = $('#' + NOM_FORMULAIRE),
            URL = "/" + PREFIX_URL + "/rencontre_equipe",
            data = new FormData(form[0]);

        // Affichage du chargement
        imageChargement(loader, 'flex');

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

        switch (data.code) {
            case 'SUCCES':
                traitement_succes(data.data);
                break;

            case 'ECHEC':
                traitement_echec(data.erreur);
                break;
        }
    };

    const traitement_succes = function (objet) {

        // On cahrge les formulaires rencontre et équipes
        contenu_rencontre.html(objet);

        // On raffraichit la table des matchs
    };

    const traitement_echec = function (erreur) {
        Swal.fire({
            title: "Erreur",
            text: erreur,
            icon: "error",
            timer: 3000
        });
        return;
    };
}

// Appel des fonctions d'action-------------

// Exécution de la fonction de l'action ajouter
action_ajouter(NOM_FORM, P_URL);

// Exécution de la fonction de l'action liste
action_liste(P_URL);

// On initialise le contenu du champ select
initialiser_select("match_dispute_championnat", "match_dispute_calendrier", URL_SELECT, PLACEHOLDER);

valider1(P_URL);
