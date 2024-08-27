<?php

namespace App\EventSubscriber;

use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChecker implements UserCheckerInterface
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if ($user->getDeletedAt()) {
            throw new CustomUserMessageAccountStatusException($this->translator->trans('authentication.deleted_account'));
        }

        if (!$user->isEnabled()) {
            throw new CustomUserMessageAccountStatusException($this->translator->trans('authentication.not_enabled_account'));
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        //
    }
}
