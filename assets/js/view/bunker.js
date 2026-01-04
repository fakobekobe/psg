// Les variables locales
let P_URL = 'bunker',
    P_URL_STATISTIQUE = 'statistique',
    NOM_FORM = "match_dispute_",
    ID_RENCONTRE = 0;
const URL_SELECT = "calendrier",
    PLACEHOLDER = "Calendrier";
    texteBtn = '<i class="typcn typcn-export" style="font-size:1.6em;"></i>	Enregistrer';


// Rédéfinition de la méthode Action ajouter

// Rédéfinition de l'Action ajouter
// Fonction qui permet d'afficher les rencontres et les équipes
function valider1(
    PREFIX_URL,
    NOM_TABLEAU = "dataTable",
    NOM_FORMULAIRE = 'form_type',
    btn_valider = 'valider_1',
    id_saison = "match_dispute_saison",
    id_calendrier = "match_dispute_calendrier",
    id_contenu_classement = 'contenu_classement',
    id_contenu_domicile = 'contenu_domicile',
    id_contenu_exterieur = 'contenu_exterieur',
) {

    let btn = $('#' + btn_valider),
        loader = $('#bloc-loader'),
        contenu_classement = $('#' + id_contenu_classement),
        contenu_domicile = $('#' + id_contenu_domicile),
        contenu_exterieur = $('#' + id_contenu_exterieur),
        btn_calendrier = $('#' + id_calendrier),
        btn_saison = $('#' + id_saison);

    btn.on('click', function (e) {
        e.preventDefault();

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
            URL = "/" + PREFIX_URL + "/classement",
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
                traitement_echec(data.message);
                break;
        }
    };

    const traitement_succes = function (objet) {

        // On cahrge les formulaires rencontre et équipes
        contenu_classement.html(objet);    
    };

    const traitement_echec = function (message) {
        Swal.fire({
            title: "Erreur",
            text: message,
            icon: "error",
            timer: 3000
        });
        return;
    };
}

// Rédéfinition de la Fonction de ciblage d'onglet
function cible_onglet(P_BTN, P_SOURCE, P_CIBLE, NOM_TABLEAU, URL, URL_STAT, NOM_TABLEAU_STAT, BTN_STAT = "enregistrer_stat") {
    // Les variables globales
    let table = $('#' + NOM_TABLEAU),
        BTN_ENRE_STAT = $('#' + BTN_STAT),
        id = 0;

    table.on('click', '.' + P_BTN + 'Btn', function (e) {
        e.preventDefault();
        id = this.dataset.id;
        // On change d'onglet
        let source_tab = $('#' + P_SOURCE + '-tab'),
            cible_tab = $('#' + P_CIBLE + '-tab'),
            source = $('#' + P_SOURCE),
            cible = $('#' + P_CIBLE);

        source_tab.removeClass('active');
        source_tab.attr('aria-selected', 'false');
        source.removeClass('show active');

        cible_tab.addClass('active');
        cible_tab.attr('aria-selected', 'true');
        cible.addClass('show active');

        // On charge les données des équipes
        let url_fetch = "/" + URL + "/periode/" + this.dataset.id;

        fetch(url_fetch)
            .then(reponse => reponse.json())
            .then(json => traitementJson(json));
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
        // On recharge le contenu des rencontres
        let stat_contenu_domicile = $('#stat_contenu_domicile'),
            stat_contenu_exterieur = $('#stat_contenu_exterieur'),
            periode = $('#match_periode');

        stat_contenu_domicile.html(data.domicile);
        stat_contenu_exterieur.html(data.exterieur);
        ID_RENCONTRE = id;
        id_modifier = 0;
        BTN_ENRE_STAT.html(texteBtn);

        // On charge les nouvelles données avec la fonction de l'action liste
        action_liste_stat(URL_STAT, NOM_TABLEAU_STAT, ID_RENCONTRE);        
        periode.focus();
    };

    const traitement_echec = function (message) {
        Swal.fire({
            title: "Erreur !",
            text: message,
            icon: "erreur",
            timer: 3000
        });
    };

}


// On initialise le contenu du champ select
initialiser_select("match_dispute_championnat", "match_dispute_calendrier", URL_SELECT, PLACEHOLDER);

//-------------- GESTION DU VOLET ANALYSE -----------------------
// Appel des fonctions d'action-------------
valider1(P_URL);
