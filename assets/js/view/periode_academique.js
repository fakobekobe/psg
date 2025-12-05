// Les variables locales
let P_URL = 'periode-academique', 
NOM_FORM = "periode_academique_", 
texteTitre = 'Ajouter une période academique';

// Appel des fonctions d'action-------------

// Exécution de la fonction de l'action ajouter
action_ajouter(NOM_FORM, P_URL); 

// Exécution de la fonction de l'action liste
action_liste(P_URL);

// Exécution de la fonction de l'action supprimer
action_supprimer(P_URL);

// Exécution de la fonction de l'action check
action_check(P_URL, NOM_FORM, "Modifier une période academique"); 

// On exécute le traitement du bouton de fermeture
fermer_formulaire();

// On exécute le traitement du bouton de close
close_formulaire();

