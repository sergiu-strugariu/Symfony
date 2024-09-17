<?php

namespace App\Event;

use App\Entity\User as AppUser;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(
        protected RouterInterface          $router,
        protected RequestStack             $requestStack
    ) {
    }

    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->isEnabled()) {
            throw new CustomUserMessageAccountStatusException("You need to activate your account to login!");
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        //
    }
}
