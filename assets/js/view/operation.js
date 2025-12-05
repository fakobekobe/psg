// Les variables locales
let P_URL = 'operation'; // A modifier *******
let NOM_FORM = "operation_type_form_"; // A modifier *******
let texteTitre = "Ajouter une opération"; // A modifier *******
const URL_SELECT = "type-operation";
const PLACEHOLDER = "Type";

//Redéfinition de fonction
let check_select = function (objet, id_nom_champ) {
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
                if(label == "categorie")
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

// Fonction de sauvegarde de la date d'enregistrement
function getDateEnre()
{
    if(dateEnre)
    {
        let date = $('#operation_type_form_dateEnre');
        date.val(dateEnre);
    }else{  
        let date = $('#operation_type_form_dateEnre');      
        dateEnre = date.val();
    }
}


// Appel des fonctions d'action-------------

// Exécution de la fonction de l'action ajouter
action_ajouter(NOM_FORM, P_URL);

// Exécution de la fonction de l'action liste
action_liste(P_URL);

// Exécution de la fonction de l'action supprimer
action_supprimer(P_URL);

// Exécution de la fonction de l'action check
action_check(P_URL, NOM_FORM, "Modifier une opération", check_select); // A modifier *******

// On exécute le traitement du bouton de fermeture
fermer_formulaire();

// On exécute le traitement du bouton de close
close_formulaire();

// On initialise le contenu du champ select
initialiser_select("operation_type_form_categorie", "operation_type_form_type", URL_SELECT, PLACEHOLDER)


