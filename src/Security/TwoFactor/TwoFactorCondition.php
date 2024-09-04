<?php

namespace App\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Condition\TwoFactorConditionInterface;

class TwoFactorCondition implements TwoFactorConditionInterface
{
    public function shouldPerformTwoFactorAuthentication(AuthenticationContextInterface $context): bool
    {
        $user = $context->getUser();
        $roles = $user->getRoles();

        // Si l'utilisateur a le rôle ROLE_ADMIN, on retourne false
        if (in_array('ROLE_ADMIN', $roles)) {
            return false;
        }

        // Sinon, on retourne true pour activer l'authentification à deux facteurs
        return true;
    }
}