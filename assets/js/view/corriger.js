// Gestion du décompte
let P_URL = 'question-evaluation/note';

// Gestion du bouton treminer
function terminer_composition(_URL, BTN_TERMINER = 'terminer') {
    let terminer = $('#' + BTN_TERMINER);

    terminer.on('click', function (e) {
        recuperation(_URL);
    });

}

function recuperation(PREFIX_URL, IDE = 'ide', NOTE = 'note', NOM_HIDDEN = 'nombre_question') {
    let champ_hidden = $('#' + NOM_HIDDEN),
        nb_question = parseInt(champ_hidden.val(), 10),
        data = new FormData(),
        loader = $('#bloc-loader');        
    const URL = "/" + PREFIX_URL;

    // Affichage du chargement
    imageChargement(loader, 'flex');

    for (let i = 0; i < nb_question; i++) {
        champ = document.getElementById(NOTE + i);
        if (champ) {
            data.append(NOTE + i, champ.value);
        }

        champ = document.getElementById(IDE + i);
        if (champ) {
            data.append(IDE + i, champ.value);
        }
    }

    data.append('nb_question', nb_question);

    fetch(URL, {
        method: 'POST',
        body: data,
    })
        .then(reponse => reponse.json())
        .then(json => traitementJson(json));

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
        // Redirection au tableau de bord
        Swal.fire({
            title: "Félicitations",
            text: "Copie corrigée avec succès",
            icon: "success",
            timer: 5000
        });
        setTimeout(function () {
            window.location.href = data;
        }, 200);
    };

    const traitement_echec = function (erreur) {
        alert(erreur);
    };
}

// Exécution des fonctions
terminer_composition(P_URL);
