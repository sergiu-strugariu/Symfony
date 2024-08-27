<?php

namespace App\Controller\Dashboard;

use App\Entity\Education;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConventionController extends AbstractController
{
    #[Route('/dashboard/conventions', name: 'dashboard_convention_index')]
    public function index(): Response
    {
        return $this->render('dashboard/education/index.html.twig', [
            'type' => Education::TYPE_CONVENTION,
            'page_title' => 'Conventions'
        ]);
    }
}
