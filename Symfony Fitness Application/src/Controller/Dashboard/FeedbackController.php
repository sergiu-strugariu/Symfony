<?php

namespace App\Controller\Dashboard;

use App\Repository\FeedbackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FeedbackController extends AbstractController
{
    #[Route('/dashboard/feedback', name: 'dashboard_feedback_index')]
    public function index(): Response
    {
        return $this->render('dashboard/feedback/index.html.twig', [
            'page_title' => 'Feedback'
        ]);
    }
    
    #[Route('/dashboard/feedback/{uuid}/view', name: 'dashboard_feedback_view')]
    public function view(FeedbackRepository $feedbackRepository, $uuid): Response {
        $feedback = $feedbackRepository->findOneBy(['uuid' => $uuid]);

        if (null === $feedback) {
            return $this->redirectToRoute('dashboard_feedback_index');
        }
        
        return $this->render('dashboard/feedback/view.html.twig', [
                    'feedback' => $feedback
        ]);
    }
}
