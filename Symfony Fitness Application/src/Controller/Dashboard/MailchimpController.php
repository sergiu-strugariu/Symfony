<?php

namespace App\Controller\Dashboard;

use App\Helper\MailchimpAPIHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MailchimpController extends AbstractController
{
    #[Route('/mailchimp', name: 'app_mailchimp')]
    public function index(MailchimpAPIHelper $mailChimp): Response
    {
        try {
            $response = $mailChimp->getListMember('sstrugariu@gmail.com');
        } catch (Exception $ex) {
            throw $ex;
        }

        return new Response('Ping: ' . json_encode($response));
    }
}
