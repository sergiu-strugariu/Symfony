<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if ($user->getDeletedAt()) {
            throw new CustomUserMessageAccountStatusException('Acest cont a fost sters');
        }

        if ($user->getStatus() == User::STATUS_INACTIVE) {
            throw new CustomUserMessageAccountStatusException('Acest cont nu este activat.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        //
    }
}
