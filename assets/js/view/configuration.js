// Les variables locales
let P_URL = 'configuration',
    NOM_FORM = P_URL + '_';


// Action ajouter
function action_ajouter(
    PREFIX_CHAMP,
    NOM_FORMULAIRE = 'form_type',
    NOM_BTN_AJOUTER = 'ajouter'
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
        // On supprime toutes les erreurs
        suppressionErreurs();

        switch (data.code) {
            case 'SUCCES':
                traitement_succes(data.message);
                break;

            case 'ECHEC':
                traitement_echec(data.erreurs);
                break;
        }
    };

    const traitement_succes = function (message) {

        let title = 'Enregistrement !';
        let text = message;

        Swal.fire({
            title: title,
            text: text,
            icon: "success",
            timer: 3000
        });

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

// Action check
function action_check(URL, P_FORM) {

    let $form = new FormData();
    const URL_FETCH = "/" + URL + "/check";
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
                traitement_succes(data.data, P_FORM);
                break;
        }
    };

    // Définition des fonctions
    const traitement_succes = function (objet, P_FORM) {
        let champ = null;

        // On initialise les champs avec la données
        $.each(objet, function (label, valeur) {

            champ = document.getElementById(P_FORM + label);
            if (champ.nodeName.toLowerCase() == 'input') {
                champ.value = valeur;
            }else if (champ.nodeName.toLowerCase() == 'select') {
                // Selection de select
                let options = Array.from(champ.options);
                options.forEach((o, i) => {
                    if (parseInt(o.value) == parseInt(valeur)) champ.selectedIndex = i;
                });
            }
        });
    };
}

function annuler(BTN_ANNULER = 'annuler', NOM_FORMULAIRE = 'form_type')
{
    let btn = $('#' + BTN_ANNULER);
    btn.on('click', function(e){
        let form = $('#' + NOM_FORMULAIRE);
        form[0].reset();
    });
}

action_ajouter(NOM_FORM);
action_check(P_URL, NOM_FORM);
annuler();



