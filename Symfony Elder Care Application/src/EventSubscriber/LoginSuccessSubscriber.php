<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginSuccessSubscriber implements EventSubscriberInterface {

    public static function getSubscribedEvents(): array {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess'
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event) {
        $redirectUrl = $event->getUser()->getLoginRedirect();
        
        if (null !== $redirectUrl) {
            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }

}
