// Les variables locales
let P_URL = 'cours-classe', 
NOM_FORM = "cours_classe_", 
texteTitre = 'Ajouter un cours par classe',
LABEL_SELECT = ['cours', 'classe'];

// Appel des fonctions d'action-------------
// Exécution de la fonction de l'action ajouter
action_ajouter(NOM_FORM, P_URL); 

// Exécution de la fonction de l'action liste
action_liste(P_URL);

// Exécution de la fonction de l'action supprimer
action_supprimer(P_URL);

// Exécution de la fonction de l'action check
action_check(P_URL, NOM_FORM, "Modifier un cours par classe", check_select, LABEL_SELECT); 

// On exécute le traitement du bouton de fermeture
fermer_formulaire();

// On exécute le traitement du bouton de close
close_formulaire();

// Chargement du select
initialiser_select(NOM_FORM + 'classe', NOM_FORM + 'discipline', P_URL, 'Discipline');
