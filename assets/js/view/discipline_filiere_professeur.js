// Les variables locales
let P_URL = 'discipline-filiere-professeur', 
NOM_FORM = "discipline_filiere_professeur_", 
texteTitre = 'Ajouter une discipline par professeur';
const URL_SELECT = "discipline-filiere";
const PLACEHOLDER = "Discipline",
    LABEL_SELECT = ['filiere', 'professeur'];


// Appel des fonctions d'action-------------

// Exécution de la fonction de l'action ajouter
action_ajouter(NOM_FORM, P_URL); 

// Exécution de la fonction de l'action liste
action_liste(P_URL);

// Exécution de la fonction de l'action supprimer
action_supprimer(P_URL);

// Exécution de la fonction de l'action check
action_check(P_URL, NOM_FORM, "Modifier une discipline par professeur", check_select, LABEL_SELECT); // A modifier *******

// On exécute le traitement du bouton de fermeture
fermer_formulaire();

// On exécute le traitement du bouton de close
close_formulaire();

// On initialise le contenu du champ select
initialiser_select("discipline_filiere_professeur_filiere", "discipline_filiere_professeur_discipline", URL_SELECT, PLACEHOLDER);

