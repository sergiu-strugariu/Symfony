<?php

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;
use App\Entity\ActivityLog;

class RequestSubscriber implements EventSubscriberInterface {

    private $security;

    public function __construct(Security $security, EntityManagerInterface $em) {
        $this->security = $security;
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array {
        return [
            RequestEvent::class => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(RequestEvent $event) {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        // check if AJAX call
        if ($request->isXmlHttpRequest()) {
            return;
        }

        $user = $this->security->getUser();
        // check if user is logged in
        if (empty($user)) {
            return;
        }

        $requestUri = $request->getRequestUri();
        // log activity
        $activityLog = new ActivityLog();
        $activityLog->setUser($user);
        $activityLog->setPage($requestUri);
        $activityLog->setNursingHome($user->getNursingHome());
        $activityLog->setCreatedAt(new \DateTime());
        // write to DB
        $this->em->persist($activityLog);
        $this->em->flush();
    }

}
