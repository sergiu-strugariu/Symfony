<?php

namespace App\Controller\Dashboard;

use App\Entity\Education;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WorkshopController extends AbstractController
{
    #[Route('/dashboard/workshops', name: 'dashboard_workshop_index')]
    public function index(): Response
    {
        return $this->render('dashboard/education/index.html.twig', [
            'type' => Education::TYPE_WORKSHOP,
            'page_title' => 'Workshops'
        ]);
    }
}
