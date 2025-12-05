// Les variables locales
let P_URL = 'groupe'; // A modifier *******
let NOM_FORM = "groupe_type_form_"; // A modifier *******
let texteTitre = 'Ajouter un groupe'; // A modifier *******

// Appel des fonctions d'action-------------

// Exécution de la fonction de l'action ajouter
action_ajouter(NOM_FORM, P_URL); // A modifier *******

// Exécution de la fonction de l'action liste
action_liste(P_URL);

// Exécution de la fonction de l'action supprimer
action_supprimer(P_URL);

// Exécution de la fonction de l'action check
action_check(P_URL, NOM_FORM, "Modifier un groupe"); // A modifier *******

// On exécute le traitement du bouton de fermeture
fermer_formulaire();

// On exécute le traitement du bouton de close
close_formulaire();

