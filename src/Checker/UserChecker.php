<?php

namespace App\Checker;

use App\Entity\Competiteur;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof Competiteur) return;

        if (!$user->isArchive()) return;

        throw new CustomUserMessageAccountStatusException ("Votre compte n'est pas activé");
    }
}