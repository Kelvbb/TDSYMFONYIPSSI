<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements \Symfony\Component\Security\Core\User\UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
        if ($user->isActive()) {
            return;
        }
        if (\in_array(User::ROLE_ADMIN, $user->getRoles(), true)) {
            return;
        }
        throw new CustomUserMessageAccountStatusException('Votre compte n\'est pas encore activé. Un administrateur doit valider votre inscription.');
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
