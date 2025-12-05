// Les variables locales
const P_URL = 'inscription',
    NOM_FORM_SELECT = "form_type",
    BTN_INSCRIRE = "inscrire",
    BTN_SELECT = "inscription_anneeacademique",
    CHECK = 'check',
    NOM_TABLE = "";
let id_anneeacademique = 0;

// Action liste
function action_liste(URL, ID_SELECT, NOM_TABLEAU = 'dataTableInscription') {
    // Les variables globales
    let URL_FETCH = "/" + URL + "/liste/" + ID_SELECT;
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

function action_annuler(NOM_FORM, NOM_SELECT, ID_SELECT) {
    $('#' + NOM_FORM)[0].reset();
    let champ = document.getElementById(NOM_SELECT);
    let options = Array.from(champ.options);
    options.forEach((o, i) => {
        if (parseInt(o.value) == parseInt(ID_SELECT)) champ.selectedIndex = i;
    });

    $('#' + NOM_SELECT).trigger('change');
}

// Action supprimer
function action_supprimer(URL, NOM_FORMULAIRE, NOM_SELECT, P_CHECK= 'check', NOM_TABLEAU = 'dataTableInscription') {
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

        /**
         * On décoche les case à cocher de la liste des élèves
         * On exécute le traitement du select anneeAcademique
         */
        decocher_checkbox(P_CHECK);
        action_annuler(NOM_FORMULAIRE, NOM_SELECT, id_anneeacademique);

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

// Définition des fonctions d'action-------------
function action_affiche_liste(
    PREFIX_URL,
    NOM_FORMULAIRE,
    NOM_SELECT,
    NOM_BODY = 'bodyEleve',
    NOM_CHAMP = 'anneeacademique',
) {
    // Les variables globales
    const URL = "/" + PREFIX_URL;
    let loader = $('#bloc-loader');

    let btn = $('#' + NOM_SELECT);

    btn.on('change', function (e) {

        // Affichage du chargement
        imageChargement(loader, 'flex');

        let form = $('#' + NOM_FORMULAIRE);
        let data = new FormData(form[0]);
        id_anneeacademique = data.get(PREFIX_URL + '[' + NOM_CHAMP + ']');

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
                traitement_echec(data.data);
                break;
        }
    };

    const traitement_succes = function (data) {

        // Les variables
        let body = $('#' + NOM_BODY);
        let corps = '';

        $.each(data, function (key, valeur) {
            corps += ` <tr>
                <td>
                    <div class="custom-control custom-checkbox small">
						<input type="checkbox" class="custom-control-input check" 
                        name="check${key}" id="check${key}" value="${valeur.id}">
                        <label class="custom-control-label" for="check${key}"></label>
					</div>
                </td>

                <td>
                    ${key + 1}
                </td>

                <td>
                    ${valeur.matricule}
                </td>

                <td>
                    ${valeur.nomComplet}
                </td>
            </tr>`;
        });

        body.html(corps);

        // On charge les nouvelles données avec la fonction de l'action liste
        action_liste(PREFIX_URL, id_anneeacademique);

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

function action_inscrire(
    PREFIX_URL,
    NOM_FORMULAIRE,
    NOM_BTN_CLICK,
    P_CHECK,
    NOM_SELECT,
    NOM_CHAMP = 'anneeacademique'
) {
    // Les variables globales
    const URL = "/" + PREFIX_URL;
    let loader = $('#bloc-loader'),
        btn = $('#' + NOM_BTN_CLICK);

    btn.on('click', function (e) {
        e.preventDefault();
        // Affichage du chargement
        imageChargement(loader, 'flex');

        let form = $('#' + NOM_FORMULAIRE);
        let data = new FormData(form[0]);
        id_anneeacademique = data.get(PREFIX_URL + '[' + NOM_CHAMP + ']');

        let liste_choix = getListeCheckbox(P_CHECK);
        // On ajoute les valeurs du tableau dans le tableau choix
        // pour éviter d'avoir une chaine en faisant data.append('choix', liste_choix);
        for (const valeur of liste_choix.values()) {
            data.append('choix[]', valeur);
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
                traitement_succes(data.data);
                break;

            case 'ECHEC':
                traitement_echec(data.data);
                break;
        }
    };

    const traitement_succes = function (data) {
        Swal.fire({
            title: "Succès",
            text: data + " élève(s) inscrit(s) avec succès.",
            icon: "success",
            timer: 3000
        });

        // On charge les nouvelles données avec la fonction de l'action liste
        //action_liste(PREFIX_URL, id_anneeacademique);
        /**
         * On décoche les case à cocher de la liste des élèves
         * On exécute le traitement du select anneeAcademique
         */
        decocher_checkbox(P_CHECK);
        action_annuler(NOM_FORMULAIRE, NOM_SELECT, id_anneeacademique);
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

// function getListeCheckbox(check_groupe) {
//     let groupes = $('.' + check_groupe);
//     let liste = [];
//     groupes.each(function (e) {
//         if ($(this).is(':checked')) {
//             liste.push($(this).val());
//         }
//     });

//     return liste;
// }

function reset(NOM_FORM, BTN_ANNULER = "annulerinscrire")
{
    $('#' + BTN_ANNULER).on('click', function(e){
        $('#' + NOM_FORM)[0].reset();
    });    
}

// Exécution de la fonction de l'action ajouter
action_affiche_liste(P_URL, NOM_FORM_SELECT, BTN_SELECT);
action_inscrire(P_URL, NOM_FORM_SELECT, BTN_INSCRIRE, CHECK, BTN_SELECT);
check_box(CHECK);
action_supprimer(P_URL, NOM_FORM_SELECT, BTN_SELECT)
reset(NOM_FORM_SELECT);