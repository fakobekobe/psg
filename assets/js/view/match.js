// Les variables locales
let P_URL = 'match',
    NOM_FORM = "match_dispute_";
const URL_SELECT = "calendrier";
const PLACEHOLDER = "Calendrier";

// Rédéfinition de la méthode Action ajouter
// Rédéfinition de l'Action liste
function action_liste(URL, NOM_TABLEAU = 'dataTable', ID_CALENDRIER = 0) {
    // Les variables globales
    let URL_FETCH = "/" + URL + "/liste/" + ID_CALENDRIER;
    let table = $('#' + NOM_TABLEAU);

    fetch(URL_FETCH)
        .then(reponse => reponse.json())
        .then(json => traitementJson(json));

    const traitementJson = function (data) {
        switch (data.code) {
            case 'SUCCES':
                traitement_succes(data.html);
                break;

            case 'ECHEC':
                traitement_echec();
                break;
        }
    };

    const traitement_succes = function (html) {
        tableau_data(table, html);
    };

    const traitement_echec = function () {
        tableau_vide(NOM_TABLEAU, table)
    };
}

// Rédéfinition de l'Action ajouter
function action_ajouter(
    PREFIX_URL,
    id_calendrier = "match_dispute_calendrier",
    id_contenu_rencontre = 'contenu_rencontre',
    NOM_TABLEAU = 'dataTable',
    NOM_BTN_AJOUTER = 'enregistrer'
) {
    // Les variables globales
    let URL = '/' + PREFIX_URL,
        loader = $('#bloc-loader'),
        contenu_rencontre = $('#' + id_contenu_rencontre),
        btn = $('#' + NOM_BTN_AJOUTER);

    btn.on('click', function (e) {
        e.preventDefault();       

        // Les variables
        let rencontre = $('.rencontre'),
            domicile = $('.domicile'),
            exterieur = $('.exterieur'),
            id_rencontre = 0,
            id_domicile = 0,
            id_exterieur = 0;

        // On vérifie si les champs ont été cochés
        rencontre.each(function (k, v) {
            if ($(this).is(':checked')) {
                id_rencontre = $(this).val();
            }
        });

        if (!id_rencontre) {
            Swal.fire({
                title: 'Erreur',
                text: 'Veuillez cocher une rencontre.',
                icon: "error",
                timer: 3000
            });
            return;
        }

        domicile.each(function (k, v) {
            if ($(this).is(':checked')) {
                id_domicile = $(this).val();
            }
        });

        if (!id_domicile) {
            Swal.fire({
                title: 'Erreur',
                text: 'Veuillez cocher une équipe à domicile.',
                icon: "error",
                timer: 3000
            });
            return;
        }

        exterieur.each(function (k, v) {
            if ($(this).is(':checked')) {
                id_exterieur = $(this).val();
            }
        });

        if (!id_exterieur) {
            Swal.fire({
                title: 'Erreur',
                text: "Veuillez cocher une équipe à l'extérieur.",
                icon: "error",
                timer: 3000
            });
            return;
        }

        // Vérification des clubs différents
        if (id_domicile == id_exterieur) {
            Swal.fire({
                title: 'Erreur',
                text: "Veuillez cocher des équipes différentes.",
                icon: "error",
                timer: 3000
            });
            return;
        }

        // Affichage du chargement
        imageChargement(loader, 'flex');

        let data = new FormData();
        data.append('rencontre', id_rencontre);
        data.append('domicile', id_domicile);
        data.append('exterieur', id_exterieur);

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

    const traitement_succes = function (data) {
        Swal.fire({
            title: 'Enregistrement !',
            text: 'Votre enregistrement a été effectué avec succès.',
            icon: "success",
            timer: 1500
        });
        console.log(data);
        // On recharge le contenu
        contenu_rencontre.html(data);

        // On charge les nouvelles données avec la fonction de l'action liste
        let btn_calendrier = $('#' + id_calendrier);
        action_liste(PREFIX_URL, NOM_TABLEAU, btn_calendrier.val());
    };

    const traitement_echec = function (erreur) {
        Swal.fire({
            title: "Erreur",
            text: erreur,
            icon: "error",
            timer: 5000
        });
        return;
    };

}

// Fonction qui permet d'afficher les rencontres et les équipes
function valider1(
    PREFIX_URL,
    NOM_TABLEAU = "dataTable",
    NOM_FORMULAIRE = 'form_type',
    btn_valider = 'valider_1',
    id_saison = "match_dispute_saison",
    id_calendrier = "match_dispute_calendrier",
    id_contenu_rencontre = 'contenu_rencontre',
    id_contenu_domicile = 'contenu_domicile',
    id_contenu_exterieur = 'contenu_exterieur',
) {

    let btn = $('#' + btn_valider),
        loader = $('#bloc-loader'),
        contenu_rencontre = $('#' + id_contenu_rencontre),
        contenu_domicile = $('#' + id_contenu_domicile),
        contenu_exterieur = $('#' + id_contenu_exterieur),
        btn_calendrier = $('#' + id_calendrier);

    btn.on('click', function (e) {
        e.preventDefault();
        let btn_saison = $('#' + id_saison);
            

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
        contenu_rencontre.html(objet.rencontre);
        contenu_domicile.html(objet.domicile);
        contenu_exterieur.html(objet.exterieur);

        // On raffraichit la table des matchs
        action_liste(PREFIX_URL, NOM_TABLEAU, btn_calendrier.val());
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

// Rédéfinition de l'Action supprimer
function action_supprimer(
    URL, 
    NOM_TABLEAU = 'dataTable', 
    id_calendrier = "match_dispute_calendrier",
    id_contenu_rencontre = 'contenu_rencontre'
) {
    // Les variables globales
    let table = $('#' + NOM_TABLEAU),
        contenu_rencontre = $('#' + id_contenu_rencontre);
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
                traitement_succes(data.data);
                break;

            case 'ECHEC':
                traitement_echec(data.message);
                break;
        }
    };

    const traitement_succes = function (data) {
        Swal.fire({
            title: "Supression !",
            text: data.message,
            icon: "success",
            timer: 1500
        });

        // On recharge le contenu des rencontres
        console.log(data.html);
        contenu_rencontre.html(data.html);

        // On charge les nouvelles données avec la fonction de l'action liste
        let btn_calendrier = $('#' + id_calendrier);
        action_liste(URL_LISTE, NOM_TABLEAU, btn_calendrier.val());
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

// Appel des fonctions d'action-------------

// Exécution de la fonction de l'action ajouter
action_ajouter(P_URL);

// On initialise le contenu du champ select
initialiser_select("match_dispute_championnat", "match_dispute_calendrier", URL_SELECT, PLACEHOLDER);

// Appel de la fonction qui permet d'afficher les rencontres et les équipes
valider1(P_URL);

// action Supprimer
action_supprimer(P_URL);
