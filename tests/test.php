<?php
function classement(int $rang) : string
    {
        $groupe = "";
        switch(true)
        {
            case in_array(needle: $rang, haystack: range(start: 1,end: 5)): $groupe = "1"; break;
            case in_array(needle: $rang, haystack: range(start: 6,end: 10)): $groupe = "2"; break;
            case in_array(needle: $rang, haystack: range(start: 11,end: 15)): $groupe = "3"; break;
            case in_array(needle: $rang, haystack: range(start: 15,end: 20)): $groupe = "4"; break;
        }
        return $groupe;
    }

    function categorie_classement(int $rang_domicile, int $rang_exterieur): string
    {
        $groupe_domicile = classement(rang: $rang_domicile);
        $groupe_exterieur = classement(rang: $rang_exterieur);        

        return $groupe_domicile . "-" . $groupe_exterieur;
    }


    var_dump(categorie_classement(rang_domicile: 1,rang_exterieur:20));