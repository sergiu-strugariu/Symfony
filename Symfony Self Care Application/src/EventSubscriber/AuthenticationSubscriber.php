<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected RouterInterface          $router,
        protected EventDispatcherInterface $dispatcher,
        protected EntityManagerInterface   $em)
    {
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => 'onLogin'];
    }

    public function onLogin(LoginSuccessEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();

        // Update last Login
        $user->setLastLoginAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();
    }
}
