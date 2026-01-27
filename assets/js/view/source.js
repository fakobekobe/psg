// ES VARIABLES GLOBALES --------------------------
let texteBtn = '<i class="typcn typcn-plus"></i> Ajouter',
 id_modifier = 0,
 id_disabled = "",
 dateEnre = "",
 id_source = {
    proposition : 0,
    correction : 0
 };

//--------------------------------------------------


// LES FONCTIONS UTILITAIRES ------------------------

// Fonction de gestion du chargement
function imageChargement(loader, css) {
    loader.css({
        display: css
    });
};

// Fonction de suppression des erreurs du formulaire
function suppressionErreurs() {
    let div = $('.invalid-feedback');
    div.each(function () {
        $(this).remove();
    });

    let input = $('.is-invalid');
    input.each(function () {
        $(this).removeClass("is-invalid");
    });
}

// BTN fermer le formulaire
function fermer_formulaire() {
    let btn = $('#annulerajouter');
    btn.on('click', function (e) {

        // On initialise le formulaire
        initialiser();

        // On supprime les erreurs du formulaire
        suppressionErreurs()
    });
}

// BTN close formulaire
function close_formulaire() {
    let btn = $('#close-form');
    btn.on('click', function (e) {
        // On initialise le formulaire
        initialiser();

        // On supprime les erreurs du formulaire
        suppressionErreurs()
    });
}

// Fonction d'initialisation du formulaire
function initialiser() {
    const NOM_TITRE = 'ajouterBackdropLabel';
    const NOM_BTN = 'ajouter';
    let titre = $('#' + NOM_TITRE);
    titre.text(texteTitre);

    let Btn = $('#' + NOM_BTN);
    Btn.html(texteBtn);

    // On initialise le formulaire avec la données
    let FORM = document.getElementsByTagName('form');
    $.each(FORM[1], function (i, element) {
        if (element.nodeName.toLowerCase() == 'input') {
            if (element.type != 'hidden') {
                if (element.type == 'checkbox') {
                    element.removeAttribute('checked');
                } else {
                    element.value = '';
                }
            }
        } else if (element.nodeName.toLowerCase() == 'select') {
            let options = Array.from(element.options);

            if (!element.getAttribute('disabled')) {
                // Selection de select
                options.forEach((o, i) => {
                    if (o.value == '') element.selectedIndex = i;
                });
            } else {
                // Selection de select
                options.forEach((o, i) => {
                    if (o.value == id_disabled) element.selectedIndex = i;
                });
            }

        } else if (element.nodeName.toLowerCase() == 'textarea') {
            element.value = "";
        }
    });

    // On initialise la variable pour la modification (On annulle la modification)
    id_modifier = 0;

    // Appel de la fonction pour initialiser la date
    getDateEnre();
}

// Fonction de sauvegarde de l'id_desabled
function getId_disabled() {
    let FORM = document.getElementsByTagName('form');
    $.each(FORM[1], function (i, element) {

        if (element.nodeName.toLowerCase() == 'select') {
            if (element.getAttribute('disabled')) {
                // Selection de select
                let options = Array.from(element.options);
                options.forEach((o, i) => {
                    if (o.getAttribute('selected')) id_disabled = o.value;
                });
            }
        }
    });

}

// Fonction de sauvegarde de la date d'enregistrement
// Cette fonction est redéfinie dans le js correspondant
function getDateEnre() {
    // Cette fonction est rédéfinie dans le js qui contient une date
}

// initialisation du champ select
function initialiser_select(select_principal, select_cible, URL, LABEL) {
    $('#' + select_principal).on('change', function (e) {
        const PATH_URL = "/" + URL + "/select";
        let form = new FormData();
        form.append('id', $(this).val());
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

/**
 * tableau_vide Permet l'affichage du message pour indiquer qu'il n'y a aucune données.
 * @param {string} nom_tableau Nom du tableau à manipuler
 * @param {array} table  Tableau ciblé (le nom du tableau avec le #)
 */
function tableau_vide(nom_tableau, table) {
    let tbody = $('#' + nom_tableau + ' tbody');
    tbody.remove();
    table.DataTable({
        language: {
            "decimal": "",
            "emptyTable": "Aucune données disponible",
            "info": "De _START_ à _END_ sur _TOTAL_ lignes",
            "infoEmpty": "De 0 à 0 sur 0 lignes",
            "infoFiltered": "(filtré depuis les _MAX_ lignes)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Affiche _MENU_ lignes",
            "loadingRecords": "Chargement...",
            "processing": "",
            "search": "Recherche:",
            "zeroRecords": "Aucune données trouvée",
            "paginate": {
                "first": "Premier",
                "last": "Dernier",
                "next": "Suivant",
                "previous": "Précédent"
            },
            "aria": {
                "orderable": "Trier par cette colonne",
                "orderableReverse": "Trie décroissant par cette colonne"
            }
        },
        destroy: true
    });
};

/* Cette methode récupère une chaine de caractère bien spécifique,
   la transforme en tableau pour le DataTable
*/
function formater_donnees(donnees) {
    var datas = [], i = 0, t = null, data = "";
    data = donnees.split("!x!");
    data.forEach(e => {
        t = e.split(";x;");
        datas[i] = t;
        i++;
    });

    return datas;
}

/**
 * tableau_data Permet l'affichage des données formatées.
 * @param {string} nom_tableau Nom du tableau à manipuler
 * @param {array} table  Tableau ciblé (le nom du tableau avec le #)
 */
function tableau_data(table, reponse) {
    const donnees = formater_donnees(reponse);
    table.DataTable({
        data: donnees,
        language: {
            "decimal": "",
            "emptyTable": "Aucune données disponible",
            "info": "De _START_ à _END_ sur _TOTAL_ lignes",
            "infoEmpty": "De 0 à 0 sur 0 lignes",
            "infoFiltered": "(filtré depuis les _MAX_ lignes)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Affiche _MENU_ lignes",
            "loadingRecords": "Chargement...",
            "processing": "",
            "search": "Recherche:",
            "zeroRecords": "Aucune données trouvée",
            "paginate": {
                "first": "Premier",
                "last": "Dernier",
                "next": "Suivant",
                "previous": "Précédent"
            },
            "aria": {
                "orderable": "Trier par cette colonne",
                "orderableReverse": "Trie décroissant par cette colonne"
            }
        },
        //buttons: ['copy', 'csv', 'excel', 'pdf', 'print'] 
        processing: true,
        destroy: true

    });
}

// Cette function permet de remonter vers le haut lorsqu'on clique sur le lien qui a la class .editBtn
function ciblage() {
    let href = $('#btn-cible').attr('href');
    $('.editBtn').each(function (e) {
        $(this).attr('href', href);
    });
}

function imprime_zone(titre, contenu, largeur = '0', hauteur = '0') {
    // Définie la zone à imprimer
    //let zi = document.getElementById(contenu).innerHTML;
    // Ouvre une nouvelle fenetre
    let f = window.open("", "ZoneImpr", "height=" + hauteur + ", width=" + largeur + ", toolbar=0, menubar=0, scrollbars=1, resizable=1,status=0, location=0, left=0, top=0");

    // Définit le Style de la page
    f.document.body.style.color = '#000000';
    f.document.body.style.backgroundColor = '#FFFFFF';
    f.document.body.style.padding = "0px";
    f.document.body.style.margin = "0px";

    // Ajoute les Données
    f.document.title = titre;
    f.document.body.innerHTML += " " + contenu + " ";
    // Imprime et ferme la fenetre
    // J'ai englobé les trois lignes dans la fonction pour permettre au logo de charger durant la 1 seconde
    setTimeout(function () {
        f.window.print();
        f.window.close();
        return true;
    }, 1000);

}

/**
 * check_box Permet de cocher et de décocher une liste de case à cocher
 * @param {*} check_groupe Id de la case à cocher source
 */
function check_box(check_groupe) {
    $('#' + check_groupe).on('change', function (e) {
        let groupes = $('.' + check_groupe);
        if ($(this).is(':checked')) {
            groupes.each(function (e) {
                $(this).prop('checked', true);
            });
        } else {
            groupes.each(function (e) {
                $(this).prop('checked', false);
            });
        }
    });
}

function decocher_checkbox(check_groupe) {
    $('#' + check_groupe).prop('checked', false);
    let groupes = $('.' + check_groupe);
    groupes.each(function (e) {
        $(this).prop('checked', false);
    });
}

function strUcFirst(a) {
    return (a + '').charAt(0).toUpperCase() + (a + '').substr(1);
}

const traitement_succes_check = function (objet, id_nom_champ, var_label = null) {
    // On sauvegarde l'id dans la variable globale pour la modification
    id_modifier = objet.id;
    let champ = null;

    // On initialise les champs avec la données
    $.each(objet, function (label, valeur) {

        if (label != 'id') {
            champ = document.getElementById(id_nom_champ + label);
            if (champ.nodeName.toLowerCase() == 'input') {
                if (champ.getAttribute('type') == 'checkbox') {
                    champ.removeAttribute('checked');
                    if (valeur) {
                        champ.setAttribute('checked', 'checked');
                    }
                } else {
                    champ.value = valeur;
                }

            } else if (champ.nodeName.toLowerCase() == 'select') {
                // Selection de select
                let options = Array.from(champ.options);
                options.forEach((o, i) => {
                    if (parseInt(o.value) == parseInt(valeur)) champ.selectedIndex = i;
                });
            } else if (champ.nodeName.toLowerCase() == 'textarea') {
                champ.value = valeur;
            }
        }
    });
};

let check_select = function (objet, id_nom_champ, p_label) {
    // On sauvegarde l'id dans la variable globale pour la modification
    id_modifier = objet.id;
    let champ = null;

    // On initialise les champs avec la données
    $.each(objet, function (label, valeur) {
        if (label != 'id') {
            champ = document.getElementById(id_nom_champ + label);
            if (champ.nodeName.toLowerCase() == 'input') {
                champ.value = valeur;
            } else if (champ.nodeName.toLowerCase() == 'select') {
                // Selection de select
                if(p_label.includes(label)) // On vérifie si label existe dans le tableau p_label
                {                    
                    let options = Array.from(champ.options);
                    options.forEach((o, i) => {
                        if (parseInt(o.value) == parseInt(valeur)) champ.selectedIndex = i;
                    });
                }else {
                    champ.innerHTML = valeur;
                }
                
            } else if (champ.nodeName.toLowerCase() == 'textarea') {
                champ.value = valeur;
            }
        }
    });
};

// Fonction de ciblage d'onglet
function cible_onglet(P_BTN, P_SOURCE, P_CIBLE, NOM_TABLEAU, P_PROPO = true) {
    // Les variables globales
    let table = $('#' + NOM_TABLEAU);

    table.on('click', '.' + P_BTN + 'Btn', function (e) {
        e.preventDefault();

        if(P_PROPO)
        {
            id_source.proposition = this.dataset.id;
        } else{
            id_source.correction = this.dataset.id;
        }
        
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

    });
}

function getListeCheckbox(check_groupe) {
    let groupes = $('.' + check_groupe);
    let liste = [];
    groupes.each(function (e) {
        if ($(this).is(':checked')) {
            liste.push($(this).val());
        }
    });

    return liste;
}

//---------------------------------------------------



// LES FONCTIONS D'ACTIONS --------------------------

// Action ajouter
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

        if (FONCTION_ECHEC == null) {
            form[0].reset();
            $('#' + NOM_BTN_ANNULER).trigger('click');

            if (id_modifier) {
                btn.html(texteBtn);
                id_modifier = 0;
            }

            // On charge les nouvelles données avec la fonction de l'action liste
            action_liste(URL_LISTE, NOM_TABLEAU);
        } else {
            const btn_matricule = $('#' + FONCTION_ECHEC[1]);
            btn_matricule.trigger('click');
            if (id_modifier) {
                let annuler_reduction = $('#annuler_reduction');
                annuler_reduction.trigger('click');
                id_modifier = 0;
            }
        }

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

// Action liste
function action_liste(URL, NOM_TABLEAU = 'dataTable') {
    // Les variables globales
    let URL_FETCH = "/" + URL + "/liste";
    let table = $('#' + NOM_TABLEAU);

    fetch(URL_FETCH)
        .then(reponse => reponse.json())
        .then(json => traitementJson(json));

    const traitementJson = function (data) {
        if (!id_disabled) {
            getId_disabled();
        }

        // Appel de la fonction d'enregistrement de la date
        getDateEnre();

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

// Action supprimer
function action_supprimer(URL, NOM_TABLEAU = 'dataTable', ACTUALISER_MATRICULE = null) {
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

        // On charge les nouvelles données avec la fonction de l'action liste
        if (ACTUALISER_MATRICULE) {
            const btn_matricule = $('#' + ACTUALISER_MATRICULE);
            btn_matricule.trigger('click');
        } else {
            action_liste(URL_LISTE, NOM_TABLEAU);
        }

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

// Action check
function action_check(
    URL,
    NOM_CHAMP,
    TEXT_TITRE,
    FONCTION_SUCCES = null,
    LABEL_P = null,
    NOM_TABLEAU = 'dataTable',
    NOM_TITRE = 'ajouterBackdropLabel',
    NOM_BTN = 'ajouter',
    CHEMIN = 'check') {
    // Les variables globales
    let table = $('#' + NOM_TABLEAU);

    table.on('click', '.editBtn', function (e) {
        e.preventDefault();

        // On sauvegarde et on modifie le titre du formulaire
        let titre = $('#' + NOM_TITRE);
        texteTitre = titre.html();
        titre.text(TEXT_TITRE);

        // On sauvegarde et on modifie le bouton du formulaire
        let Btn = $('#' + NOM_BTN);
        texteBtn = Btn.html();
        Btn.html('<i class="typcn typcn-edit"></i> Modifier');

        let $form = new FormData();
        const id = this.dataset.id;
        $form.append('id', id);
        let URL_FETCH = "/" + URL + "/" + CHEMIN + "/" + id;

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
                    FONCTION_SUCCES(data.objet, NOM_CHAMP, LABEL_P);
                    break;

                case 'ECHEC':
                    traitement_echec(data.message);
                    break;
            }
        };

        // Définition des fonctions
        const traitement_echec = function (message) {
            Swal.fire({
                title: "Traitement !",
                text: message,
                icon: "danger",
                timer: 3000
            });
        };

        // Condition de récupération de la fonction de succes par défaut
        FONCTION_SUCCES = FONCTION_SUCCES ? FONCTION_SUCCES : traitement_succes_check;
    });
}

function action_check_direct(PARM_URL, NOM_CHAMP, P_TABLE = "dataTable", NOM_BTN = 'ajouter') {
    let table = $('#' + P_TABLE);

    table.on('click', '.editBtn', function (e) {
        let $form = new FormData();
        const id = this.dataset.id;
        $form.append('id', id);
        let URL_FETCH = "/" + PARM_URL + "/check/" + id;

        // On sauvegarde et on modifie le bouton du formulaire
        let Btn = $('#' + NOM_BTN);
        texteBtn = Btn.html();
        Btn.html('<i class="typcn typcn-edit"></i> Modifier');

        fetch(URL_FETCH, {
            method: 'POST',
            body: $form
        })
            .then(reponse => reponse.json())
            .then(json => traitementJson(json));

        const traitementJson = function (data) {
            switch (data.code) {
                case 'SUCCES':
                    traitement_succes_check(data.objet, NOM_CHAMP);
                    break;
            }
        };
    });
}

// Action afficher
function action_afficher(URL, FONCTION_SUCCES = null, NOM_TABLEAU = 'dataTable') {
    // Les variables globales
    let table = $('#' + NOM_TABLEAU);

    table.on('click', '.showBtn', function (e) {

        let $form = new FormData();
        const id = this.dataset.id;
        $form.append('id', id);
        let URL_FETCH = "/" + URL + "/afficher";

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
                    FONCTION_SUCCES(data.html);
                    break;

                case 'ECHEC':
                    traitement_echec();
                    break;
            }
        };

        // Définition des fonctions
        const traitement_succes = function (objet) {
            let nomComplet = $('#d-nomcomplet'),
                email = $('#d-email'),
                actif = $('#d-actif'),
                verifie = $('#d-verifie'),
                groupe = $('#d-groupe'),
                liste = "<ul>";

            nomComplet.html(objet.nomComplet);
            email.html(objet.email);
            actif.html(objet.actif);
            verifie.html(objet.verifie);

            objet.groupes.forEach(function (valeur, key) {
                liste += '<li>' + strUcFirst(valeur) + '</li>'
            });

            liste += "</ul>";
            groupe.html(liste);
        };

        const traitement_echec = function () {
            Swal.fire({
                title: "Détails !",
                text: 'Aucune donnée trouvée',
                icon: "danger",
                timer: 3000
            });
        };
        // Condition de récupération de la fonction de succes par défaut
        FONCTION_SUCCES = FONCTION_SUCCES ? FONCTION_SUCCES : traitement_succes;
    });
}

//---------------------------------------------------