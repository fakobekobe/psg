// Les variables locales
let P_URL = 'match',
    NOM_FORM = "match_dispute_",
    ID_RENCONTRE = 0,
    texteTitre = 'Enregistrer';
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

// Rédéfinition de la Fonction de ciblage d'onglet
function cible_onglet(P_BTN, P_SOURCE, P_CIBLE, NOM_TABLEAU, URL) {
    // Les variables globales
    let table = $('#' + NOM_TABLEAU),
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
            stat_contenu_exterieur = $('#stat_contenu_exterieur');
        stat_contenu_domicile.html(data.domicile);
        stat_contenu_exterieur.html(data.exterieur);
        ID_RENCONTRE = id;
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


// Appel des fonctions d'action-------------

// Exécution de la fonction de l'action ajouter
action_ajouter(P_URL);

// On initialise le contenu du champ select
initialiser_select("match_dispute_championnat", "match_dispute_calendrier", URL_SELECT, PLACEHOLDER);

// Appel de la fonction qui permet d'afficher les rencontres et les équipes
valider1(P_URL);

// action Supprimer
action_supprimer(P_URL);

//-------------- GESTION DU VOLET STATISTIQUES -----------------------

// Définition des fonctions
function select_periode() {
    let periode = $('#match_periode'),
        score_d = $('#score_d'),
        score_e = $('#score_e'),
        possession_d = $('#possession_d'),
        possession_e = $('#possession_e'),
        total_tir_d = $('#total_tir_d'),
        total_tir_e = $('#total_tir_e'),
        tir_cadre_d = $('#tir_cadre_d'),
        tir_cadre_e = $('#tir_cadre_e'),
        grosse_chance_d = $('#grosse_chance_d'),
        grosse_chance_e = $('#grosse_chance_e'),
        corner_d = $('#corner_d'),
        corner_e = $('#corner_e'),
        carton_jaune_d = $('#carton_jaune_d'),
        carton_jaune_e = $('#carton_jaune_e'),
        carton_rouge_d = $('#carton_rouge_d'),
        carton_rouge_e = $('#carton_rouge_e'),
        hors_jeu_d = $('#hors_jeu_d'),
        hors_jeu_e = $('#hors_jeu_e'),
        coup_franc_d = $('#coup_franc_d'),
        coup_franc_e = $('#coup_franc_e'),
        touche_d = $('#touche_d'),
        touche_e = $('#touche_e'),
        faute_d = $('#faute_d'),
        faute_e = $('#faute_e'),
        tacle_d = $('#tacle_d'),
        tacle_e = $('#tacle_e'),
        arret_d = $('#arret_d'),
        arret_e = $('#arret_e');

    periode.on('change', function (e) {
        score_d.val(0);
        score_e.val(0);
        possession_d.val(0);
        possession_e.val(0);
        total_tir_d.val(0);
        total_tir_e.val(0);
        tir_cadre_d.val(0);
        tir_cadre_e.val(0);
        grosse_chance_d.val(0);
        grosse_chance_e.val(0);
        corner_d.val(0);
        corner_e.val(0);
        carton_jaune_d.val(0);
        carton_jaune_e.val(0);
        carton_rouge_d.val(0);
        carton_rouge_e.val(0);
        hors_jeu_d.val(0);
        hors_jeu_e.val(0);
        coup_franc_d.val(0);
        coup_franc_e.val(0);
        touche_d.val(0);
        touche_e.val(0);
        faute_d.val(0);
        faute_e.val(0);
        tacle_d.val(0);
        tacle_e.val(0);
        arret_d.val(0);
        arret_e.val(0);
    });


}

// Rédéfinition de l'Action check
function action_check_direct(PARM_URL, NOM_CHAMP, P_TABLE = "dataTableStat", NOM_BTN = 'enregistrer_stat') {
    let table = $('#' + P_TABLE);

    table.on('click', '.statEditBtn', function (e) {
        let $form = new FormData();
        const id = this.dataset.id;
        $form.append('id', id);
        let URL_FETCH = "/" + PARM_URL + "/check/" + id;

        // On sauvegarde et on modifie le bouton du formulaire
        let Btn = $('#' + NOM_BTN);
        texteBtn = Btn.html();
        Btn.html('<i class="typcn typcn-edit" style="font-size:1.6em;"></i> Modifier');

        fetch(URL_FETCH, {
            method: 'POST',
            body: $form
        })
            .then(reponse => reponse.json())
            .then(json => traitementJson(json));

        const traitementJson = function (data) {
            switch (data.code) {
                case 'SUCCES':
                    traitement_succes(data.objet);
                    break;
            }
        };
    });

    const traitement_succes = function (data) {
        let periode = $('#match_periode'),
        score_d = $('#score_d'),
        score_e = $('#score_e'),
        possession_d = $('#possession_d'),
        possession_e = $('#possession_e'),
        total_tir_d = $('#total_tir_d'),
        total_tir_e = $('#total_tir_e'),
        tir_cadre_d = $('#tir_cadre_d'),
        tir_cadre_e = $('#tir_cadre_e'),
        grosse_chance_d = $('#grosse_chance_d'),
        grosse_chance_e = $('#grosse_chance_e'),
        corner_d = $('#corner_d'),
        corner_e = $('#corner_e'),
        carton_jaune_d = $('#carton_jaune_d'),
        carton_jaune_e = $('#carton_jaune_e'),
        carton_rouge_d = $('#carton_rouge_d'),
        carton_rouge_e = $('#carton_rouge_e'),
        hors_jeu_d = $('#hors_jeu_d'),
        hors_jeu_e = $('#hors_jeu_e'),
        coup_franc_d = $('#coup_franc_d'),
        coup_franc_e = $('#coup_franc_e'),
        touche_d = $('#touche_d'),
        touche_e = $('#touche_e'),
        faute_d = $('#faute_d'),
        faute_e = $('#faute_e'),
        tacle_d = $('#tacle_d'),
        tacle_e = $('#tacle_e'),
        arret_d = $('#arret_d'),
        arret_e = $('#arret_e');
    
        score_d.val(data.score_d);
        score_e.val(data.score_e);
        possession_d.val(data.possession_d);
        possession_e.val(data.possession_e);
        total_tir_d.val(data.total_tir_d);
        total_tir_e.val(data.total_tir_e);
        tir_cadre_d.val(data.tir_cadre_d);
        tir_cadre_e.val(data.tir_cadre_e);
        grosse_chance_d.val(data.grosse_chance_d);
        grosse_chance_e.val(data.grosse_chance_e);
        corner_d.val(data.corner_d);
        corner_e.val(data.corner_e);
        carton_jaune_d.val(data.carton_jaune_d);
        carton_jaune_e.val(data.carton_jaune_e);
        carton_rouge_d.val(data.carton_rouge_d);
        carton_rouge_e.val(data.carton_rouge_e);
        hors_jeu_d.val(data.hors_jeu_d);
        hors_jeu_e.val(data.hors_jeu_e);
        coup_franc_d.val(data.coup_franc_d);
        coup_franc_e.val(data.coup_franc_e);
        touche_d.val(data.touche_d);
        touche_e.val(data.touche_e);
        faute_d.val(data.faute_d);
        faute_e.val(data.faute_e);
        tacle_d.val(data.tacle_d);
        tacle_e.val(data.tacle_e);
        arret_d.val(data.arret_d);
        arret_e.val(data.arret_e);

        periode.val(data.periode);

        ID_RENCONTRE = data.rencontre;
        id_modifier = data.rencontre;

        periode.focus();        
    };
}

// Fonction ajouter  statistique
function action_ajouter_stat(
    PREFIX_URL,
    NOM_TABLEAU = 'dataTableStat',
    NOM_BTN_AJOUTER = 'enregistrer_stat',
) {
    // Les variables globales    
    const URL_LISTE = PREFIX_URL;
    let PREFIX_URL_U = '/' + PREFIX_URL + '/modifier/',
        URL = '/' + PREFIX_URL + '/statistique',
        loader = $('#bloc-loader');

    let btn = $('#' + NOM_BTN_AJOUTER);

    btn.on('click', function (e) {
        e.preventDefault();

        if (!ID_RENCONTRE) {
            Swal.fire({
                title: 'Erreur',
                text: 'Veuillez cliquer sur le bouton statistiques du match.',
                icon: "error",
                timer: 3000
            });
            return;
        }        

        // Les variables
        let data = new FormData();
        periode = $('#match_periode');

        if(!periode.val())
        {
            Swal.fire({
                title: 'Erreur',
                text: 'Veuillez sélectionner la période.',
                icon: "error",
                timer: 3000
            });
            return;
        }

        // Affichage du chargement
        imageChargement(loader, 'flex');

        let score_d = $('#score_d'),
            score_e = $('#score_e'),
            possession_d = $('#possession_d'),
            possession_e = $('#possession_e'),
            total_tir_d = $('#total_tir_d'),
            total_tir_e = $('#total_tir_e'),
            tir_cadre_d = $('#tir_cadre_d'),
            tir_cadre_e = $('#tir_cadre_e'),
            grosse_chance_d = $('#grosse_chance_d'),
            grosse_chance_e = $('#grosse_chance_e'),
            corner_d = $('#corner_d'),
            corner_e = $('#corner_e'),
            carton_jaune_d = $('#carton_jaune_d'),
            carton_jaune_e = $('#carton_jaune_e'),
            carton_rouge_d = $('#carton_rouge_d'),
            carton_rouge_e = $('#carton_rouge_e'),
            hors_jeu_d = $('#hors_jeu_d'),
            hors_jeu_e = $('#hors_jeu_e'),
            coup_franc_d = $('#coup_franc_d'),
            coup_franc_e = $('#coup_franc_e'),
            touche_d = $('#touche_d'),
            touche_e = $('#touche_e'),
            faute_d = $('#faute_d'),
            faute_e = $('#faute_e'),
            tacle_d = $('#tacle_d'),
            tacle_e = $('#tacle_e'),
            arret_d = $('#arret_d'),
            arret_e = $('#arret_e');

        data.append('score_d', score_d.val(0));
        data.append('score_e', score_e.val(0));
        data.append('possession_d', possession_d.val(0));
        data.append('possession_e', possession_e.val(0));
        data.append('total_tir_d', total_tir_d.val(0));
        data.append('total_tir_e', total_tir_e.val(0));
        data.append('tir_cadre_d', tir_cadre_d.val(0));
        data.append('tir_cadre_e', tir_cadre_e.val(0));
        data.append('grosse_chance_d', grosse_chance_d.val(0));
        data.append('grosse_chance_e', grosse_chance_e.val(0));
        data.append('corner_d', corner_d.val(0));
        data.append('corner_e', corner_e.val(0));
        data.append('carton_jaune_d', carton_jaune_d.val(0));
        data.append('carton_jaune_e', carton_jaune_e.val(0));
        data.append('carton_rouge_d', carton_rouge_d.val(0));
        data.append('carton_rouge_e', carton_rouge_e.val(0));
        data.append('hors_jeu_d', hors_jeu_d.val(0));
        data.append('hors_jeu_e', hors_jeu_e.val(0));
        data.append('coup_franc_d', coup_franc_d.val(0));
        data.append('coup_franc_e', coup_franc_e.val(0));
        data.append('touche_d', touche_d.val(0));
        data.append('touche_e', touche_e.val(0));
        data.append('faute_d', faute_d.val(0));
        data.append('faute_e', faute_e.val(0));
        data.append('tacle_d', tacle_d.val(0));
        data.append('tacle_e', tacle_e.val(0));
        data.append('arret_d', arret_d.val(0));
        data.append('arret_e', arret_e.val(0));

        data.append('periode', periode.val(0));
        data.append('rencontre', ID_RENCONTRE);


        // On enregistre les données


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

        switch (data.code) {
            case 'SUCCES':
                alert(data.data);
                return;
                traitement_succes();
                break;

            case 'ECHEC':
                traitement_echec(data.erreur);
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
        }

        Swal.fire({
            title: title,
            text: text,
            icon: "success",
            timer: 1500
        });

        if (id_modifier) {
            btn.html(texteBtn);
            id_modifier = 0;
        }

        // On charge les nouvelles données avec la fonction de l'action liste
        //action_liste_stat(URL_LISTE, NOM_TABLEAU);
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
//--------------------


// On cible l'onglet statistique
cible_onglet('stat', P_URL, 'statistique', 'dataTable', P_URL);

// appel de ma fonction select période
select_periode();

// appel de la fonction ajouter statistique
action_ajouter_stat(P_URL);
