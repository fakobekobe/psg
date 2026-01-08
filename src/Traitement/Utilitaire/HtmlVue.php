<?php

namespace  App\Src\Traitement\Utilitaire;

use App\Traitement\Utilitaire\Utilitaire;

class HtmlVue
{
    public static function classement(mixed ...$donnees): string
    {
        $logo = Utilitaire::afficher_image_circulaire(path: $donnees[2], class: $donnees[13]);
        $dernier = HtmlVue::icones(donnees: $donnees[12]);
        return <<<HTML
        <tr class="{$donnees[0]}">
            <td class = "{$donnees[14]} {$donnees[15]}">
                {$donnees[1]}
            </td>
            <td class ="{$donnees[14]}">
                $logo
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[3]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[4]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[5]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[6]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[7]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[8]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[9]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[10]}
            </td>
            <td class ="{$donnees[14]}">
                {$donnees[11]}
            </td>
            <td class ="{$donnees[14]}">
               $dernier
            </td>
        </tr>
HTML;
    }

    public static function icone(int $code): string
    {
        $retour = "";
        switch ($code) {
            case Utilitaire::VICTOIRE:
                $retour = '<i class="typcn typcn-input-checked text-success"></i>';
                break;
            case Utilitaire::NUL:
                $retour = '<i class="typcn typcn-minus text-dark"></i>';
                break;
            case Utilitaire::DEFAITE:
                $retour = '<i class="typcn typcn-delete text-danger"></i>';
                break;
        }

        return $retour;
    }

    public static function icones(array $donnees): string
    {
        $retour = "";

        foreach ($donnees as $data) {
            $retour .= HtmlVue::icone(code: $data);
        }

        return $retour;
    }
}
