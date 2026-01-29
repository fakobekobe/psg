<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class AuthentificationVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedHttpException(
                message: "Vous n'avez pas les droits nécessaires pour effectuer cette action.",
                code: 403
            );
        }

        // On récupère la liste des rôles de l'utilisateur
        $roles = $user->getRoles();
        
        // On vérifie si l'utilisateur possède le role nécessaire
        if(! in_array(needle: $attribute, haystack: $roles))
        {
            throw new AccessDeniedHttpException(
                message: "Vous n'avez pas les droits nécessaires pour effectuer cette action.",
                code: 403
            );
        }else{
            return true;
        }
    }
}
