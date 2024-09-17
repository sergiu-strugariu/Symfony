<?php
namespace App\Event;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    protected $requestStack;

    public function __construct(
        protected RouterInterface          $router,
        protected EventDispatcherInterface $dispatcher,
        protected EntityManagerInterface   $em,
        RequestStack                       $requestStack
    ) {
        $this->requestStack = $requestStack;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => 'onLogin'];
    }

    public function onLogin(LoginSuccessEvent $event): RedirectResponse
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $session->getFlashBag()->add('success', 'You are now logged in!');

        return new RedirectResponse($this->router->generate("app_home"));
    }
}
