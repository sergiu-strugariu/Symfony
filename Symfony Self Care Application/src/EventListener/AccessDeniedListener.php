<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedListener implements AccessDeniedHandlerInterface
{
    /**
     * @var RouterInterface
     */
    private RouterInterface $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @param AccessDeniedException $accessDeniedException
     * @return Response|null
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        return new RedirectResponse($this->router->generate('dashboard_my_account'));
    }
}
