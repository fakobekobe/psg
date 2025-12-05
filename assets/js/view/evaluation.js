// Les variables locales
let P_URL = 'evaluation', 
NOM_FORM = P_URL + "_", 
texteTitre = 'Ajouter une évaluation';
const URL_SELECT_FILIERE = "discipline-filiere",
    PLACEHOLDER_DISCIPLINE = "Discipline",
    URL_SELECT_CYCLE = "periode-academique",
    URL_SELECT_CLASSE = "classe",
    URL_SELECT_FI = "filiere-annee-courante",
    PLACEHOLDER_PERIODE = "Période",
    PLACEHOLDER_CLASSE = "Classe",
    PLACEHOLDER_FILIERE = "Filière",
    LABEL_SELECT = ['cycle', 'typeevaluation'];


// Appel des fonctions d'action-------------

// Exécution de la fonction de l'action ajouter
action_ajouter(NOM_FORM, P_URL); 

// Exécution de la fonction de l'action liste
action_liste(P_URL);

// Exécution de la fonction de l'action supprimer
action_supprimer(P_URL);

// Exécution de la fonction de l'action check
action_check(P_URL, NOM_FORM, "Modifier une évaluation", check_select, LABEL_SELECT); // A modifier *******

// On exécute le traitement du bouton de fermeture
fermer_formulaire();

// On exécute le traitement du bouton de close
close_formulaire();

// On initialise le contenu du champ select discipline
initialiser_select(P_URL + "_filiere", P_URL + "_discipline", URL_SELECT_FILIERE, PLACEHOLDER_DISCIPLINE);
initialiser_select(P_URL + "_filiere", P_URL + "_classe", URL_SELECT_CLASSE, PLACEHOLDER_CLASSE);
initialiser_select(P_URL + "_cycle", P_URL + "_periode", URL_SELECT_CYCLE, PLACEHOLDER_PERIODE);
initialiser_select(P_URL + "_cycle", P_URL + "_filiere", URL_SELECT_FI, PLACEHOLDER_FILIERE);

