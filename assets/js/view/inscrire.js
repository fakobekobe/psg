// Les variables locales
let P_URL = 'versement'; 
let P_URL_VERSEMENT = 'versements'; 
let NOM_FORM = "inscrire_type_form_"; 
let NOM_FORM_VERSEMENT = "versement_type_form_"; 
let texteTitre = "Ajouter une inscription"; // 
const ID_TYPE_FORM = "form_type_inscrire",
    NOM_BTN_VERSEMENT = 'btn_matricule_versement', // Partie Versement ------------
    NOM_TABLE = "dataTableInscription",
    NOM_TABLE_VERSEMENT = "dataTableVersement",
    ID_BTN_ANNULER = "annuler_versement",
    CHAMP_IGNORE = NOM_FORM_VERSEMENT + 'matricule',
    ID_FORM_ = 'form_type_versement',
    ID_DATE_ = NOM_FORM_VERSEMENT + 'dateVersement',
    BTN_AJOUTER_VERSEMENT = "ajouter_versement",
    P_URL_REDUCTION = "reduction", // Partie Réduction ---------------
    NOM_FORM_REDUCTION = "reduction_type_form_",
    ID_FORM_REDUCTION = 'form_type_reduction',
    CHAMP_IGNORE_REDUCTION = NOM_FORM_REDUCTION + 'matricule',
    NOM_TABLE_REDUCTION = "dataTableReduction",
    NOM_BTN_REDUCTION = 'btn_matricule_reduction',
    BTN_AJOUTER_REDUCTION = "ajouter_reduction";

// Fonction de sauvegarde de la date d'enregistrement
function getDateEnre(ID_DATE = 'inscrire_type_form_dateInscription') {
    if (dateEnre) {
        let date = $('#' + ID_DATE);
        date.val(dateEnre);
    } else {
        let date = $('#' + ID_DATE);
        dateEnre = date.val();
    }
}

// Appel des fonctions d'action-------------

// Exécution de la fonction de l'action ajouter
action_ajouter(NOM_FORM, P_URL, ID_TYPE_FORM, NOM_TABLE);

// Exécution de la fonction de l'action liste
action_liste(P_URL, NOM_TABLE);

// Exécution de la fonction de l'action supprimer
action_supprimer(P_URL, NOM_TABLE);

// Exécution de la fonction de l'action check
action_check(P_URL, NOM_FORM, "Modifier une inscription", null, NOM_TABLE); 

// On exécute le traitement du bouton de fermeture
fermer_formulaire();

// On exécute le traitement du bouton de close
close_formulaire();

// Action Importer
function action_importer(
    PREFIX_URL,
    NOM_FORMULAIRE = 'type_form_importer',
    NOM_TABLEAU = 'dataTableInscription',
    NOM_BTN_AJOUTER = 'importer'
) {
    // Les variables globales
    let loader = $('#bloc-loader');

    let btn = $('#' + NOM_BTN_AJOUTER);

    btn.on('click', function (e) {
        e.preventDefault();
        // Affichage du chargement
        imageChargement(loader, 'flex');

        let form = $('#' + NOM_FORMULAIRE);
        let URL = form[0].action;
        let data = new FormData(form[0]);

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
        let form = $('#' + NOM_FORMULAIRE);
        form[0].reset();
        $('#annulerimporter').trigger('click');

        // On charge les nouvelles données avec la fonction de l'action liste
        action_liste(PREFIX_URL, NOM_TABLEAU);

        Swal.fire({
            title: 'Enregistrement !',
            text: data + ' inscription(s) effectuée(s) avec succès.',
            icon: "success",
            timer: 3000
        });

    };

    const traitement_echec = function (erreur) {
        Swal.fire({
                title: "Erreur",
                text: erreur,
                icon: "error",
                timer: 3000
            });
    };
}

action_importer(P_URL);

// Gestion des versements ---------------------------------------------------------
// Les nouvelles fonctions ---------------------------

// Ciblage onglet versement
function cible_onglet(P_BTN = "versement", NOM_TABLEAU = 'dataTableInscription') {
    // Les variables globales
    let table = $('#' + NOM_TABLEAU);

    table.on('click', '.' + P_BTN + 'Btn', function (e) {
        e.preventDefault();

        let inscription_tab = $('#inscription-tab'),
            versement_tab = $('#' + P_BTN + '-tab'),
            inscription = $('#inscription'),
            versement = $('#' + P_BTN),
            matricule = $('#' + P_BTN + '_type_form_matricule'),
            btn_matricule_versement = $('#btn_matricule_' + P_BTN);

        inscription_tab.removeClass('active');
        inscription_tab.attr('aria-selected', 'false');
        inscription.removeClass('show active');

        versement_tab.addClass('active');
        versement_tab.attr('aria-selected', 'true');
        versement.addClass('show active');

        // On initialise le champ
        matricule.val(this.dataset.matricule);
        btn_matricule_versement.trigger('click');
    });
}


// Initialisation du formulaire et du statut
function execute_initialisation(ID_FORM, ID_DATE, CHAMP_IGNORE = null) {
    // On initialise le formulaire
    initialiser_data(ID_FORM, ID_DATE, CHAMP_IGNORE);

    // On initialise le statut
    initialisation_statut();
}

// BTN annuler formulaire
function annuler_formulaire(ID_BTN, ID_FORM, ID_DATE) {
    let btn = $('#' + ID_BTN);
    btn.on('click', function (e) {
        // On initialise le formulaire et le statut
        execute_initialisation(ID_FORM, ID_DATE)
    });
}

// Fonction d'initialisation du formulaire
function initialiser_data(ID_FORM, ID_DATE, IGNORE_CHAMP = null) {

    // On initialise le formulaire avec la données
    let FORM = document.getElementById(ID_FORM);

    $.each(FORM, function (i, element) {
        if (element.nodeName.toLowerCase() == 'input') {
            if (!IGNORE_CHAMP || IGNORE_CHAMP != element.getAttribute('id')) {
                element.value = '';
            }
        } else if (element.nodeName.toLowerCase() == 'select') {
            let options = Array.from(element.options);
            // Selection de select
            options.forEach((o, i) => {
                if (o.value == '') element.selectedIndex = i;
            });
        } else if (element.nodeName.toLowerCase() == 'textarea') {
            element.value = "";
        }
    });

    // On initialise les infos
    const td_total_versements = $('#td_total_versements'),
        td_scolarite_total = $('#td_scolarite_total'),
        td_total_verse = $('#td_total_verse'),
        td_remise = $('#td_remise'),
        td_reste_payer = $('#td_reste_payer');

    td_total_versements.text('');
    td_scolarite_total.text('');
    td_total_verse.text('');
    td_remise.text('');
    td_reste_payer.text('');

    // Appel de la fonction pour initialiser la date
    getDateEnre(ID_DATE);
}

function initialisation_statut(ETAT = 0) {
    let texte = $('#statut_texte'),
        icon = $('#statut_icon');
    const c_info = 'typcn-info',
        c_down = 'typcn-thumbs-down',
        c_up = 'typcn-thumbs-up';

    switch (ETAT) {
        case 0: // Défaut        
            texte.text("AUCUN STATUT");
            texte.css({
                color: 'black'
            });
            icon.removeClass(c_down + ' ' + c_up);
            icon.addClass(c_info);
            icon.css({
                color: 'black'
            });
            break;
        case 1: // En cours
            texte.text("EN COURS");
            texte.css({
                color: 'red'
            });
            icon.removeClass(c_info + ' ' + c_up);
            icon.addClass(c_down);
            icon.css({
                color: 'red'
            });
            break;
        case 2: // Soldé
            texte.text("SOLDE");
            texte.css({
                color: 'green'
            });
            icon.removeClass(c_info + ' ' + c_down);
            icon.addClass(c_up);
            icon.css({
                color: 'green'
            });
            break;
    }
}

// Action rechercher le matricule
function action_rechercher_matricule(URL, NOM_BTN, NOM_CHAMP, ID_FORM, ID_DATE = null, IGNORE_CHAMP = null, FONCTION_SUCCES = null) {
    // Les variables globales
    let Btn = $('#' + NOM_BTN),
        champ_recherche = $('#' + NOM_CHAMP + 'matricule'),
        matricule = '';

    Btn.on('click', function (e) {
        e.preventDefault();
                            
        matricule = champ_recherche.val();
        if (matricule == '') {
            traitement_echec('Veuillez renseigner un matricule.');
            return;
        }

        let $form = new FormData();
        $form.append('matricule', matricule);
        let URL_FETCH = "/" + URL + "/check/" + matricule;

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
                    FONCTION_SUCCES(data.objet, NOM_CHAMP, URL, ID_FORM);
                    break;

                case 'ECHEC':
                    traitement_echec(data.message);
                    break;
            }
        };

        // Définition des fonctions
        const traitement_succes = function (objet, id_nom_champ, P_URL, P_ID_FORM) {
            let champ = null;
            // On initialise les champs avec la données
            $.each(objet.objet, function (label, valeur) {
                champ = document.getElementById(id_nom_champ + label);
                champ.value = valeur;
            });

            // On initialise les infos
            const td_total_versements = $('#td_total_versements'),
                td_scolarite_total = $('#td_scolarite_total'),
                td_total_verse = $('#td_total_verse'),
                td_remise = $('#td_remise'),
                td_reste_payer = $('#td_reste_payer'),
                devise = ' F';
            let etat = 0;

            td_total_versements.text(objet.infos.totalVersement);
            td_scolarite_total.text(objet.infos.scolariteTotal + devise);
            td_total_verse.text(objet.infos.totalVerse + devise);
            td_remise.text(objet.infos.remise + devise);
            td_reste_payer.text(objet.infos.restePayer + devise);

            etat = parseInt(objet.infos.restePayer) ? 1 : 2;
            // On initialise le statut
            initialisation_statut(etat);

            // On affiche la liste dans le tableau
            action_liste_versement(P_URL, P_ID_FORM);
        };

        function traitement_echec(message) {
            Swal.fire({
                title: "Traitement !",
                text: message,
                icon: "error",
                timer: 1500
            });

            // On initialise le formulaire et le statut
            execute_initialisation(ID_FORM, ID_DATE, IGNORE_CHAMP);

            // On affiche la liste dans le tableau
            action_liste_versement(URL, ID_FORM);
        }

        FONCTION_SUCCES = FONCTION_SUCCES ? FONCTION_SUCCES : traitement_succes;
    });
}

// Définition des fonctions de traitement succes de la réduction pour l'action rechercher matricule
const traitement_succes_reduction = function (objet = null, id_nom_champ = null, P_URL, P_ID_FORM) {
    let champ = null;
    // On initialise les champs avec la données
    $.each(objet, function (label, valeur) {
        champ = document.getElementById(id_nom_champ + label);
        champ.value = valeur; 
    });
    // On affiche la liste dans le tableau
    action_liste_versement(P_URL, P_ID_FORM, "dataTableReduction");
};

const traitement_echec_versement = function (erreurs = null) {
    let errors = '';
    $.each(erreurs, function (key, valeur) {
        errors += valeur + "\n";
    });

    Swal.fire({
        title: 'Erreur',
        text: errors,
        icon: "error",
        timer: 3000
    });
};

// Action liste
function action_liste_versement(URL, NOM_FORM, NOM_TABLEAU = 'dataTableVersement') {
    // Les variables globales
    let URL_FETCH = "/" + URL + "/liste",
        table = $('#' + NOM_TABLEAU),
        form = $('#' + NOM_FORM),
        formdata = new FormData(form[0]);

    //formdata.append('id', );

    fetch(URL_FETCH, {
        method: 'POST',
        body: formdata
    })
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

// Action imprimer
function action_imprimer(URL, TABLE, titre) {
    let tableau = $('#' + TABLE);
    const URL_FETCH = "/" + URL + "/imprimer";

    tableau.on('click', '.printBtn', function (e) {
        e.preventDefault();

        let form = new FormData();
        form.append('id', this.dataset.id);

        fetch(URL_FETCH, {
            method: 'POST',
            body: form
        })
            .then(reponse => reponse.json())
            .then(json => traitementJson(json));

        // Fonction de gestion du traitement du retour des données json du fetch
        function traitementJson(data) {
            switch (data.code) {
                case 'SUCCES':
                    traitement_succes(data.html);
                    break;

                case 'ECHEC':
                    traitement_echec(data.message);
                    break;
            }
        };

        const traitement_succes = function (html) {
            imprime_zone(titre, html);
        };

        const traitement_echec = function (message) {
            Swal.fire({
                title: "Impression !",
                text: message,
                icon: "danger",
                timer: 1500
            });
        };

    });
}

// BTN Réinitialiser formulaire
function reinitialiser_formulaire(ID_BTN, ID_FORM) {
    let btn = $('#' + ID_BTN);
    btn.on('click', function (e) {
        let form = $('#' + ID_FORM);
        form[1].reset();
        let legendajouter = $('#legendajouter'),
            ajouter_reduction = $('#ajouter_reduction');
        legendajouter.text('Ajouter une réduction');
        ajouter_reduction.html('<i class="typcn typcn-plus"></i>Ajouter');
        id_modifier = 0;
    });
}

// On lance les fonctions -------------------------
cible_onglet();
cible_onglet(P_URL_REDUCTION)
annuler_formulaire(ID_BTN_ANNULER, ID_FORM_, ID_DATE_);
action_rechercher_matricule(P_URL_VERSEMENT, NOM_BTN_VERSEMENT, NOM_FORM_VERSEMENT, ID_FORM_, ID_DATE_, CHAMP_IGNORE);
action_ajouter(
    NOM_FORM_VERSEMENT,
    P_URL_VERSEMENT,
    ID_FORM_,
    NOM_TABLE_VERSEMENT,
    BTN_AJOUTER_VERSEMENT,
    [traitement_echec_versement, NOM_BTN_VERSEMENT]
);
action_supprimer(P_URL_VERSEMENT, NOM_TABLE_VERSEMENT, NOM_BTN_VERSEMENT);
action_imprimer(P_URL_VERSEMENT, NOM_TABLE_VERSEMENT, "Test Impression");

// Gestion des Réductions ---------------------------------------------------
action_rechercher_matricule(P_URL_REDUCTION, NOM_BTN_REDUCTION, NOM_FORM_REDUCTION, ID_FORM_REDUCTION, null, CHAMP_IGNORE_REDUCTION, traitement_succes_reduction);
reinitialiser_formulaire('annuler_reduction', ID_FORM_REDUCTION);
action_ajouter(
    NOM_FORM_REDUCTION,
    P_URL_REDUCTION,
    ID_FORM_REDUCTION,
    NOM_TABLE_REDUCTION,
    BTN_AJOUTER_REDUCTION,
    [traitement_echec_versement, NOM_BTN_REDUCTION]
);
action_supprimer(P_URL_REDUCTION, NOM_TABLE_REDUCTION, NOM_BTN_REDUCTION);
action_check(P_URL_REDUCTION, NOM_FORM_REDUCTION, "Modifier une réduction", null, NOM_TABLE_REDUCTION, 'legendajouter', BTN_AJOUTER_REDUCTION, "verification"); 

// Gestion du volet Recherche (POINT DE LA CAISSE)--------------------------------------------------------
// initialisation du champ select
function chargement_donnees(URL, select_classe, select_utilisateur, date_debut, date_fin) {
    const PATH_URL = "/" + URL + "/chargement";
        let form = new FormData();
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
            }
        };

        const traitement_succes = function (html) {
            $('#' + select_classe).html(html['classe']);
            $('#' + select_utilisateur).html(html['utilisateur']);
            $('#' + date_debut).val(html['date']);
            $('#' + date_fin).val(html['date']);
        };
}

// Action recherche
function action_recherche(
    NOM_FORMULAIRE = 'form_type_recherche',
    NOM_TABLEAU = 'table_rechercher',
    NOM_BTN_AJOUTER = 'ajouter_recherche'
) {
    // Les variables globales
    let table = $('#' + NOM_TABLEAU);
    let loader = $('#bloc-loader');
    let montant_ttc = $('#montant_ttc');
    let total_remise = $('#total_remise');
    let reste_payer = $('#reste_payer');
    let scolarite_total = $('#scolarite_total');

    let btn = $('#' + NOM_BTN_AJOUTER);

    btn.on('click', function (e) {
        e.preventDefault();
        // Affichage du chargement
        imageChargement(loader, 'flex');

        let form = $('#' + NOM_FORMULAIRE);
        let URL = form[0].action;

        let data = new FormData(form[0]);

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
                traitement_succes(data.html);
                break;

            case 'ECHEC':
                traitement_echec();
                break;
        }
    };

    const traitement_succes = function (html) {
        montant_ttc.val(html.montant);
        scolarite_total.val(html.scolaritetotal);
        total_remise.val(html.remise);
        reste_payer.val(html.restepayer);

        tableau_data(table, html.versement);
    };

    const traitement_echec = function () {
        montant_ttc.val(0);
        scolarite_total.val(0);
        total_remise.val(0);
        reste_payer.val(0);
        tableau_vide(NOM_TABLEAU, table)
    };
}

chargement_donnees(P_URL, 'classe_r', 'utilisateur_r', 'date_debut_r', 'date_fin_r');
action_recherche();
