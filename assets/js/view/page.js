// Les variables locales
let P_URL = 'admin/page'; // A modifier *******

// Définitaion des fonctions Action ajouter
function action_ajouter(
    PREFIX_URL,
    NOM_BTN_AJOUTER
) {
    // Les variables globales
    let PREFIX_URL_U = '/' + PREFIX_URL;
    let loader = $('#bloc-loader');

    let btn = $('#' + NOM_BTN_AJOUTER);

    btn.on('click', function (e) {
        e.preventDefault();
        // Affichage du chargement
        imageChargement(loader, 'flex');

        let data = new FormData();

        fetch(PREFIX_URL_U, {
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
                traitement_succes();
                break;

            case 'ECHEC':
                traitement_echec(data.message);
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

        // Exécution de la fonction de l'action liste
        action_liste(PREFIX_URL);
    };

    const traitement_echec = function (message) {
        Swal.fire({
            title: "Erreur",
            text: message,
            icon: "error",
            timer: 3000
        });
    };
}

// Appel des fonctions
action_ajouter(P_URL, 'btn_ajouter_page');

// Exécution de la fonction de l'action liste
action_liste(P_URL);


