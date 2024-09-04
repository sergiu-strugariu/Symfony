<?php

namespace App\Controller\Frontend;

use App\Entity\Faq;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FaqController extends AbstractController
{
    #[Route('/faq', name: 'app_faq')]
    public function index(EntityManagerInterface $em): Response
    {
        $faqs = $em->getRepository(Faq::class)->findAll();
        return $this->render('frontend/default/faq.html.twig', [
            'faqs' => $faqs,
        ]);
    }
}
