// Les variables locales
const P_URL = 'admin/droit-groupe-page'; 

// Définition des fonctions d'action-------------
function formulaire(PARM_URL) {
    const URL = '/' + PARM_URL + '/formulaire';
    let form = new FormData();

    fetch(URL, {
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
        }
    };

    const traitement_succes = function (html) {
        $('#bloc-groupe').html(html.groupe);
        $('#bloc-droit').html(html.droit);
        $('#bloc-page').html(html.page);
    };

}

// Action ajouter
function action_ajouter(
    PREFIX_URL,
    P_GROUPE,
    P_DROIT,
    P_PAGE,
    NOM_BTN_AJOUTER = 'btn-appliquer-groupe-gerant',
) {
    // Les variables globales
    let URL = '/' + PREFIX_URL,
        PREFIX_URL_U = '/' + PREFIX_URL + '/modifier/',
        loader = $('#bloc-loader'),
        form_groupe = $('#' + P_GROUPE),
        form_droit= $('#' + P_DROIT),
        form_page = $('#' + P_PAGE);

    let btn = $('#' + NOM_BTN_AJOUTER);

    btn.on('click', function (e) {
        e.preventDefault();
        // Affichage du chargement
        imageChargement(loader, 'flex');

        let data = new FormData(form_groupe[0]);
        let data_droit = new FormData(form_droit[0]);
        let data_page = new FormData(form_page[0]);

        data_droit.delete('cocher_droit');
        for (const valeur of data_droit.values()) {
            data.append('droit[]', valeur);
        }

        data_page.delete('cocher_page');
        for (const valeur of data_page.values()) {
            data.append('page[]', valeur);
        }
        
        if (id_modifier) {
            URL = PREFIX_URL_U + id_modifier;
        }

        fetch(URL, {
            method: 'POST',
            body: data
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
                traitement_echec(data.erreurs);
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
            id_modifier = 0;
        }

        Swal.fire({
            title: title,
            text: text,
            icon: "success",
            timer: 1500
        });

        // On initialise le texte du bouton
        $('#btn-appliquer-groupe-gerant span[class=text]').text('Appliquer la sélection');

        // Exécution de la fonction de l'action liste
        action_liste(PREFIX_URL);

    };

    const traitement_echec = function (erreurs) {
        Swal.fire({
            title: "Erreur",
            text: erreurs,
            icon: "error",
            timer: 3000
        });
    };
}

function action_check(PARM_URL, P_TABLE = "dataTable") {
    let table = $('#' + P_TABLE);

    table.on('click', '.editBtn', function (e) {
            
        // On décoche la champs
        decocher_checkbox('cocher_groupe');
        decocher_checkbox('cocher_droit');
        decocher_checkbox('cocher_page');

        let $form = new FormData();
        const id = this.dataset.id;
        $form.append('id', id);
        let URL_FETCH = "/" + PARM_URL + "/check/" + id;

        fetch(URL_FETCH, {
            method: 'POST',
            body: $form
        })
            .then(reponse => reponse.json())
            .then(json => traitementJson(json));

        const traitementJson = function (data) {
            switch (data.code) {
                case 'SUCCES':
                    traitement_succes(data.html);
                    break;
            }
        };

        // Définition des fonctions
        const traitement_succes = function (html) {
            // On sauvegarde l'id dans la variable globale pour la modification
            id_modifier = id;
            $('#btn-appliquer-groupe-gerant span[class=text]').text('Modifier la sélection');
            $('#bloc-groupe').html(html.groupe);
            $('#bloc-droit').html(html.droit);
            $('#bloc-page').html(html.page);
        }

    });
}


// Appel des fonctions d'action-------------
formulaire(P_URL);

// Cocher et décocher les cases 
check_box('cocher_groupe');
check_box('cocher_droit');
check_box('cocher_page');

// Annuler la sélection
$('#btn-annuler-groupe-gerant').on('click', function (e) {
    e.preventDefault();
    id_modifier = 0;
    $('#btn-appliquer-groupe-gerant span[class=text]').text('Appliquer la sélection');
    decocher_checkbox('cocher_groupe');
    decocher_checkbox('cocher_droit');
    decocher_checkbox('cocher_page');
});

// Exécution de la fonction de l'action liste
action_liste(P_URL);

// Exécution de la fonction de l'action ajouter
action_ajouter(P_URL, 'form-bloc-groupe', 'form-bloc-droit', 'form-bloc-page');

// Exécution de la fonction de l'action supprimer
action_supprimer(P_URL);

// Exécution de la fonction de l'action check
action_check(P_URL);

