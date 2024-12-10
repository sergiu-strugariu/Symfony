<?php

namespace App\Controller\Frontend;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function index(EntityManagerInterface $em): Response
    {
        /** @var Page $page */
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => 'home']);

        return $this->render('frontend/pages/index.html.twig', [
            'page' => $page
        ]);
    }
}
