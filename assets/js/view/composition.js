// Gestion du décompte
let decompte = $('#decompte'),
    temps = parseInt(decompte.text(),10),
    id_temps = 0,
    P_URL = 'question-evaluation/resultat';

function decompteur(BTN_TERMINER = 'terminer')
{
    temps -= 1;
    decompte.text(temps);

    if(temps == 0) 
    {
        let terminer = $('#' + BTN_TERMINER);
        terminer.trigger('click');
    };
}

id_temps = setInterval(decompteur, 60000); // 60000 = 1 minute

// Gestion du bouton treminer
function terminer_composition(_URL, ID_TIMER, BTN_TERMINER = 'terminer')
{
    let terminer = $('#' + BTN_TERMINER);

    terminer.on('click', function(e){
        clearTimeout(ID_TIMER);
        Swal.fire({
            title: "Félicitations",
            text: "Fin de la composition",
            icon: "success",
            timer: 20000
        });

        recuperation_checkbox(_URL);
    });

} 

function recuperation_checkbox(PREFIX_URL, CHECK = 'check', TEXT = 'texte', NOM_HIDDEN = 'nombre_question', NOM_HIDDEN_EVALUATION = 'id_evaluation')
{
    let champ_hidden = $('#' + NOM_HIDDEN),
        champ_id_evaluation = $('#' + NOM_HIDDEN_EVALUATION),
        nb_question = parseInt(champ_hidden.val(),10),
        liste = [],
        data = new FormData();
    const URL = "/" + PREFIX_URL;
    
    for(let i = 0; i < nb_question; i++)
    {
        liste = getListeCheckbox(CHECK + i);

        if(liste.length > 0){
            // On ajoute les valeurs du tableau dans le tableau choix
            // pour éviter d'avoir une chaine en faisant data.append('choix', liste_choix);
            for (const valeur of liste.values()) {
                data.append('choix'+ i +'[]', valeur);
            }
        }else{
            champ = document.getElementById(TEXT + i);
            if (champ && champ.nodeName.toLowerCase() == 'textarea') {
                data.append('texte'+ i, champ.value);
            }
        }
        
    }

    // On ajoute l'id_evluation
    data.append('id', champ_id_evaluation.val());

    fetch(URL, {
            method: 'POST',
            body: data,
        })
            .then(reponse => reponse.json())
            .then(json => traitementJson(json));

    // Fonction de gestion du traitement du retour des données json du fetch
    function traitementJson(data) {
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
        setTimeout(function(){
            window.location.href = data;
        }, 2000);
    };

    const traitement_echec = function (erreur) {
        alert(erreur);
    };
}

// Exécution des fonctions
terminer_composition(P_URL, id_temps);
