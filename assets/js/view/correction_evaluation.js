// Les variables locales
let P_URL = 'correction', 
    URL_FORM = "evaluation/correction";
const PLACEHOLDER_PERIODE = "Période",
    PLACEHOLDER_EVALUATION = "Evaluation";


// Modification des fonctions -------------
function initialiser_select_2(select_principal, select_cible, URL, LABEL, classe_select) {
    $('#' + select_principal).on('change', function (e) {
        const PATH_URL = "/" + URL + "/select";
        let form = new FormData(),
            classe = $('#' + classe_select);
        form.append('id', $(this).val());
        form.append('label', LABEL);
        form.append('id_classe', classe.val());

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

function action_ajouter(
    PREFIX_URL,
    CHAMP_FORMULAIRE,
    NOM_TABLEAU = 'dataTable',
    NOM_BTN_AJOUTER = 'valider'
) {
    // Les variables globales
    const URL_LISTE = '/' + PREFIX_URL;
    let loader = $('#bloc-loader'),
        champ = $('#' + CHAMP_FORMULAIRE),
        table = $('#' + NOM_TABLEAU);

    let btn = $('#' + NOM_BTN_AJOUTER);

    btn.on('click', function (e) {
        e.preventDefault();
        // Affichage du chargement
        imageChargement(loader, 'flex');

        let data = new FormData();
            data.append('id', champ.val());

        fetch(URL_LISTE, {
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
                tableau_data(table, data.html);
                break;

            case 'ECHEC':
                tableau_vide(NOM_TABLEAU, table);
                break;
        }
    };
}

// Exécution de la fonction de l'action ajouter
action_ajouter(URL_FORM, P_URL + "_evaluation"); 

// On initialise le contenu du champ select discipline
initialiser_select(P_URL + "_classe", P_URL + "_periode", P_URL, PLACEHOLDER_PERIODE);
initialiser_select_2(P_URL + "_periode", P_URL + "_evaluation", P_URL, PLACEHOLDER_EVALUATION, P_URL + "_classe");

