<?php

namespace App\Controller\Frontend;

use League\OAuth2\Client\Provider\Google;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class OAuthController extends AbstractController
{
    #[Route('/google/redirect', name: 'app_google_redirect')]
    public function redirectGoogle(): RedirectResponse
    {
        $provider = new Google([
           'clientId' => getenv('GOOGLE_CLIENT_ID'),
           'clientSecret' => getenv('GOOGLE_CLIENT_SECRET'),
           'redirectUri' => getenv('GOOGLE_REDIRECT_URI'),
        ]);

        return $this->redirect($provider->getAuthorizationUrl());
    }

    #[Route('/google/callback', name: 'app_google_callback')]
    public function callback(Request $request)
    {
        dd($request->request->all());
    }
}
