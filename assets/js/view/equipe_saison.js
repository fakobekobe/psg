// Les variables locales
let P_URL = 'equipe-saison', 
NOM_FORM = "equipe_saison_", 
texteTitre = 'Ajouter un club';
const URL_SELECT = "equipe";
const PLACEHOLDER = "Equipe";
let LABEL_SELECT = ['saison', 'championnat', 'entraineur'];


// Appel des fonctions d'action-------------

// Exécution de la fonction de l'action ajouter
action_ajouter(NOM_FORM, P_URL); 

// Exécution de la fonction de l'action liste
action_liste(P_URL);

// Exécution de la fonction de l'action supprimer
action_supprimer(P_URL);

// Exécution de la fonction de l'action check
action_check(P_URL, NOM_FORM, "Modifier un club", check_select, LABEL_SELECT); // A modifier *******

// On exécute le traitement du bouton de fermeture
fermer_formulaire();

// On exécute le traitement du bouton de close
close_formulaire();

// On initialise le contenu du champ select
initialiser_select("equipe_saison_championnat", "equipe_saison_equipe", URL_SELECT, PLACEHOLDER);

